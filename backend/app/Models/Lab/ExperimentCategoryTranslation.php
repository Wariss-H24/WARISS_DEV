<?php

namespace App\Models\Lab;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Nom et slug d'une catégorie d'expérimentations dans une langue.
 *
 * @property int $id
 * @property int $experiment_category_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentCategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentCategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentCategoryTranslation whereExperimentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentCategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentCategoryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentCategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentCategoryTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperimentCategoryTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('experiment_category_translations')]
#[Fillable(['name', 'slug'])]
class ExperimentCategoryTranslation extends Model
{
    //
}
