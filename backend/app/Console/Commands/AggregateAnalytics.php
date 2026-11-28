<?php

namespace App\Console\Commands;

use App\Services\Analytics\AnalyticsAggregator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

/**
 * Résume la mesure d'audience d'une journée (D17). Planifiée chaque nuit pour la veille ;
 * peut être relancée sans risque (les agrégats de la journée sont remplacés).
 */
#[Signature('analytics:aggregate {date? : Journée à agréger (AAAA-MM-JJ), la veille par défaut} {--days=1 : Nombre de journées à agréger en remontant depuis cette date}')]
#[Description('Agrège la mesure d\'audience d\'une ou plusieurs journées dans analytics_daily_stats')]
class AggregateAnalytics extends Command
{
    public function handle(AnalyticsAggregator $aggregator): int
    {
        try {
            $day = $this->argument('date') === null
                ? CarbonImmutable::yesterday()
                : CarbonImmutable::createFromFormat('!Y-m-d', (string) $this->argument('date'));
        } catch (Throwable) {
            $day = null;
        }

        $days = (int) $this->option('days');

        if (! $day instanceof CarbonImmutable || $days < 1 || $days > 366) {
            $this->error('Date attendue au format AAAA-MM-JJ, et --days entre 1 et 366.');

            return self::INVALID;
        }

        for ($offset = 0; $offset < $days; $offset++) {
            $current = $day->subDays($offset);
            $count = $aggregator->store($current);
            $this->line("{$current->toDateString()} : {$count} agrégats.");
        }

        return self::SUCCESS;
    }
}
