<?php

namespace App\Models\Services;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Libellé et description d'une caractéristique de service dans une langue.
 *
 * @property int $id
 * @property int $service_feature_id
 * @property string $locale
 * @property string $label
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFeatureTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFeatureTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFeatureTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFeatureTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFeatureTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFeatureTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFeatureTranslation whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFeatureTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFeatureTranslation whereServiceFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceFeatureTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('service_feature_translations')]
#[Fillable(['label', 'description'])]
class ServiceFeatureTranslation extends Model
{
    //
}
