<?php

namespace App\Models\Portfolio;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Nom et slug d'une catégorie de projets dans une langue.
 *
 * @property int $id
 * @property int $project_category_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectCategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectCategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectCategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectCategoryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectCategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectCategoryTranslation whereProjectCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectCategoryTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectCategoryTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('project_category_translations')]
#[Fillable(['name', 'slug'])]
class ProjectCategoryTranslation extends Model
{
    //
}
