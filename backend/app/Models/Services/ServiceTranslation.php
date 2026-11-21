<?php

namespace App\Models\Services;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Textes d'un service dans une langue. search_vector est calculée par PostgreSQL (D-08)
 * et n'est jamais sérialisée.
 *
 * @property int $id
 * @property int $service_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string $summary
 * @property string $description
 * @property string|null $target_audience
 * @property string|null $cta_label
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $search_vector
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereCtaLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereSearchVector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereTargetAudience($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('service_translations')]
#[Fillable(['title', 'slug', 'summary', 'description', 'target_audience', 'cta_label'])]
#[Hidden(['search_vector'])]
class ServiceTranslation extends Model
{
    //
}
