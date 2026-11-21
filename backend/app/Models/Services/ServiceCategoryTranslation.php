<?php

namespace App\Models\Services;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Nom, slug et description d'une catégorie de services dans une langue.
 *
 * @property int $id
 * @property int $service_category_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation whereServiceCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceCategoryTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('service_category_translations')]
#[Fillable(['name', 'slug', 'description'])]
class ServiceCategoryTranslation extends Model
{
    //
}
