<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Titre, slug, extrait et contenu Markdown d'un article dans une langue.
 *
 * search_vector est calculée par PostgreSQL (D-08) et n'est jamais sérialisée.
 *
 * @property int $id
 * @property int $post_id
 * @property string $locale
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $search_vector
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation whereSearchVector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('post_translations')]
#[Fillable(['title', 'slug', 'excerpt', 'content'])]
#[Hidden(['search_vector'])]
class PostTranslation extends Model
{
    //
}
