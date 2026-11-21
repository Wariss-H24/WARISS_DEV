<?php

namespace App\Models\Pages;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Titre, slug (pages personnalisées uniquement) et résumé d'une page dans une langue.
 *
 * @property int $id
 * @property int $page_id
 * @property string $locale
 * @property string $title
 * @property string|null $slug
 * @property string|null $summary
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('page_translations')]
#[Fillable(['title', 'slug', 'summary'])]
class PageTranslation extends Model
{
    //
}
