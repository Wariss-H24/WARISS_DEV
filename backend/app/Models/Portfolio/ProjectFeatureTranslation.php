<?php

namespace App\Models\Portfolio;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Titre et description d'une fonctionnalité de projet dans une langue.
 *
 * @property int $id
 * @property int $project_feature_id
 * @property string $locale
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectFeatureTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectFeatureTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectFeatureTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectFeatureTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectFeatureTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectFeatureTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectFeatureTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectFeatureTranslation whereProjectFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectFeatureTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectFeatureTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('project_feature_translations')]
#[Fillable(['title', 'description'])]
class ProjectFeatureTranslation extends Model
{
    //
}
