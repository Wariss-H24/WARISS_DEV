<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Nom et slug d'un tag dans une langue.
 *
 * @property int $id
 * @property int $tag_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagTranslation whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TagTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('tag_translations')]
#[Fillable(['name', 'slug'])]
class TagTranslation extends Model
{
    //
}
