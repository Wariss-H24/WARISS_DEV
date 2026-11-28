<?php

namespace App\Services\Analytics;

use App\Enums\AnalyticsEventName;
use App\Models\Analytics\DailyStat;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Agrégats quotidiens de la mesure d'audience (D17, D-16).
 *
 * Les données brutes (page_views, events) ne sont gardées que 90 jours ; chaque jour est résumé
 * dans analytics_daily_stats, conservé sans limite. Une journée est une journée du fuseau de
 * l'application (UTC). Le calcul est idempotent : relancer une journée remplace ses agrégats.
 *
 * Métriques : page_views et visitors (empreintes de session distinctes du jour), ventilées par
 * chemin, langue, provenance, appareil, pays, campagne et contenu ; events par nom ; shares par
 * réseau et par contenu.
 */
class AnalyticsAggregator
{
    /**
     * Dimensions des pages vues : type de dimension => expression SQL (constantes internes).
     *
     * @var array<string, string>
     */
    private const PAGE_VIEW_DIMENSIONS = [
        'path' => 'left(path, 255)',
        'locale' => 'locale',
        'referrer_type' => 'referrer_type',
        'referrer_host' => 'referrer_host',
        'device_type' => 'device_type',
        'country_code' => 'country_code',
        'utm_source' => 'utm_source',
        'utm_medium' => 'utm_medium',
        'utm_campaign' => 'utm_campaign',
        'content' => "viewable_type || ':' || viewable_id",
    ];

    /**
     * Calcule les agrégats d'une journée sans les enregistrer.
     *
     * @return list<array{metric: string, dimension_type: string, dimension_value: string, value: int}>
     */
    public function compute(CarbonImmutable $day): array
    {
        $start = $day->startOfDay();
        $end = $start->addDay();
        $views = fn () => DB::table('page_views')->where('viewed_at', '>=', $start)->where('viewed_at', '<', $end);
        $events = fn () => DB::table('events')->where('occurred_at', '>=', $start)->where('occurred_at', '<', $end);
        $shares = fn () => $events()->where('name', AnalyticsEventName::Share->value);

        $rows = [
            $this->row('page_views', '', '', $views()->count()),
            $this->row('visitors', '', '', (int) $views()->distinct()->count('session_hash')),
            $this->row('shares', '', '', $shares()->count()),
        ];

        foreach (self::PAGE_VIEW_DIMENSIONS as $dimension => $expression) {
            array_push($rows, ...$this->grouped($views(), 'page_views', $dimension, $expression));
        }

        array_push($rows, ...$this->grouped($events(), 'events', 'name', 'name'));
        array_push($rows, ...$this->grouped($shares(), 'shares', 'network', "properties->>'network'"));
        array_push($rows, ...$this->grouped($shares(), 'shares', 'content', "eventable_type || ':' || eventable_id"));

        return $rows;
    }

    /**
     * Calcule et enregistre les agrégats d'une journée (remplace ceux déjà présents).
     *
     * @return int nombre de lignes d'agrégats enregistrées
     */
    public function store(CarbonImmutable $day): int
    {
        $rows = array_values(array_filter($this->compute($day), fn (array $row): bool => $row['value'] > 0 || $row['dimension_type'] === ''));
        $date = $day->toDateString();
        $now = now();

        DB::transaction(function () use ($rows, $date, $now): void {
            DailyStat::query()->whereDate('stat_date', $date)->delete();

            foreach (array_chunk($rows, 500) as $chunk) {
                DailyStat::query()->insert(array_map(fn (array $row): array => [
                    ...$row,
                    'stat_date' => $date,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $chunk));
            }
        });

        return count($rows);
    }

    /**
     * @param  Builder  $query
     * @return list<array{metric: string, dimension_type: string, dimension_value: string, value: int}>
     */
    private function grouped($query, string $metric, string $dimension, string $expression): array
    {
        return $query
            ->selectRaw("{$expression} AS dimension_value, count(*) AS total")
            ->whereRaw("{$expression} IS NOT NULL")
            ->groupByRaw($expression)
            ->get()
            ->map(fn (object $row): array => $this->row($metric, $dimension, (string) $row->dimension_value, (int) $row->total))
            ->values()
            ->all();
    }

    /**
     * @return array{metric: string, dimension_type: string, dimension_value: string, value: int}
     */
    private function row(string $metric, string $dimension, string $value, int $count): array
    {
        return ['metric' => $metric, 'dimension_type' => $dimension, 'dimension_value' => $value, 'value' => $count];
    }
}
