<?php

namespace App\Services\Analytics;

use App\Models\Analytics\DailyStat;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Rapport d'audience sur une période : agrégats enregistrés pour les jours passés, calcul en direct
 * pour la journée en cours (pas encore agrégée). « visitors » additionne les visiteurs distincts de
 * chaque jour : l'empreinte de session change chaque jour, un visiteur fidèle compte donc une fois
 * par jour de visite (aucun suivi d'un jour à l'autre, D-16).
 */
class AnalyticsReport
{
    /**
     * Ventilations renvoyées dans « top » : clé de la réponse => [métrique, dimension].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const BREAKDOWNS = [
        'paths' => ['page_views', 'path'],
        'contents' => ['page_views', 'content'],
        'referrer_types' => ['page_views', 'referrer_type'],
        'referrer_hosts' => ['page_views', 'referrer_host'],
        'devices' => ['page_views', 'device_type'],
        'countries' => ['page_views', 'country_code'],
        'locales' => ['page_views', 'locale'],
        'utm_sources' => ['page_views', 'utm_source'],
        'utm_campaigns' => ['page_views', 'utm_campaign'],
        'share_networks' => ['shares', 'network'],
        'shared_contents' => ['shares', 'content'],
    ];

    public function __construct(private readonly AnalyticsAggregator $aggregator) {}

    /**
     * @return array<string, mixed>
     */
    public function build(CarbonImmutable $from, CarbonImmutable $to, int $limit = 10): array
    {
        $rows = $this->rows($from->startOfDay(), $to->startOfDay());
        $totals = $rows->where('dimension_type', '');

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'totals' => [
                'page_views' => (int) $totals->where('metric', 'page_views')->sum('value'),
                'visitors' => (int) $totals->where('metric', 'visitors')->sum('value'),
                'shares' => (int) $totals->where('metric', 'shares')->sum('value'),
                'events' => $this->sumBy($rows->where('metric', 'events')->where('dimension_type', 'name'))->all(),
            ],
            'daily' => $this->daily($from, $to, $totals),
            'top' => collect(self::BREAKDOWNS)
                ->map(fn (array $breakdown): array => $this->top($rows, $breakdown[0], $breakdown[1], $limit))
                ->all(),
        ];
    }

    /**
     * @return Collection<int, array{date: string, metric: string, dimension_type: string, dimension_value: string, value: int}>
     */
    private function rows(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $today = CarbonImmutable::today();

        $stored = DailyStat::query()
            ->whereDate('stat_date', '>=', $from->toDateString())
            ->whereDate('stat_date', '<=', $to->min($today->subDay())->toDateString())
            ->get(['stat_date', 'metric', 'dimension_type', 'dimension_value', 'value'])
            ->map(fn (DailyStat $stat): array => [
                'date' => $stat->stat_date->toDateString(),
                'metric' => $stat->metric,
                'dimension_type' => $stat->dimension_type,
                'dimension_value' => $stat->dimension_value,
                'value' => $stat->value,
            ]);

        if ($to->lessThan($today) || $from->greaterThan($today)) {
            return $stored->values();
        }

        $live = collect($this->aggregator->compute($today))
            ->map(fn (array $row): array => ['date' => $today->toDateString(), ...$row]);

        return $stored->concat($live)->values();
    }

    /**
     * @param  Collection<int, array{date: string, metric: string, dimension_type: string, dimension_value: string, value: int}>  $totals
     * @return list<array{date: string, page_views: int, visitors: int, shares: int}>
     */
    private function daily(CarbonImmutable $from, CarbonImmutable $to, Collection $totals): array
    {
        $byDate = $totals->groupBy('date');
        $days = [];

        foreach (CarbonPeriod::create($from->startOfDay(), $to->startOfDay()) as $day) {
            $date = $day->toDateString();
            $dayRows = $byDate->get($date, collect());

            $days[] = [
                'date' => $date,
                'page_views' => (int) $dayRows->where('metric', 'page_views')->sum('value'),
                'visitors' => (int) $dayRows->where('metric', 'visitors')->sum('value'),
                'shares' => (int) $dayRows->where('metric', 'shares')->sum('value'),
            ];
        }

        return $days;
    }

    /**
     * @param  Collection<int, array{date: string, metric: string, dimension_type: string, dimension_value: string, value: int}>  $rows
     * @return list<array{value: string, count: int}>
     */
    private function top(Collection $rows, string $metric, string $dimension, int $limit): array
    {
        return $this->sumBy($rows->where('metric', $metric)->where('dimension_type', $dimension))
            ->sortDesc()
            ->take($limit)
            ->map(fn (int $count, int|string $value): array => ['value' => (string) $value, 'count' => $count])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, array{date: string, metric: string, dimension_type: string, dimension_value: string, value: int}>  $rows
     * @return Collection<int|string, int>
     */
    private function sumBy(Collection $rows): Collection
    {
        return $rows->groupBy('dimension_value')->map(fn (Collection $group): int => (int) $group->sum('value'));
    }
}
