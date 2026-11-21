<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Nom, slug et description d'une catégorie d'articles dans une langue.
 *
 * @property int $id
 * @property int $post_category_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation wherePostCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCategoryTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('post_category_translations')]
#[Fillable(['name', 'slug', 'description'])]
class PostCategoryTranslation extends Model
{
    //
}
