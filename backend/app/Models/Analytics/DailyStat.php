<?php

namespace App\Models\Analytics;

use Database\Factories\Analytics\DailyStatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Agrégat quotidien d'audience (domaine D17), sans aucune donnée identifiante,
 * calculé chaque jour à partir des données brutes et conservé après leur purge.
 *
 * Exemple : stat_date = 2026-11-21, metric = page_views, dimension_type = referrer_type,
 * dimension_value = social, value = 42. Sans dimension, dimension_type et dimension_value sont vides.
 *
 * @property int $id
 * @property Carbon $stat_date
 * @property string $metric
 * @property string $dimension_type
 * @property string $dimension_value
 * @property int $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\Analytics\DailyStatFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat whereDimensionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat whereDimensionValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat whereMetric($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat whereStatDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyStat whereValue($value)
 *
 * @mixin \Eloquent
 */
#[Table('analytics_daily_stats')]
#[Fillable(['stat_date', 'metric', 'dimension_type', 'dimension_value', 'value'])]
class DailyStat extends Model
{
    /** @use HasFactory<DailyStatFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stat_date' => 'date',
            'value' => 'integer',
        ];
    }
}
