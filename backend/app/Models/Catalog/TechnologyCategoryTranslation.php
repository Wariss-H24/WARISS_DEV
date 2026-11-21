<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Nom et slug d'une catégorie de technologies dans une langue.
 *
 * @property int $id
 * @property int $technology_category_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyCategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyCategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyCategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyCategoryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyCategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyCategoryTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyCategoryTranslation whereTechnologyCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TechnologyCategoryTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('technology_category_translations')]
#[Fillable(['name', 'slug'])]
class TechnologyCategoryTranslation extends Model
{
    //
}
