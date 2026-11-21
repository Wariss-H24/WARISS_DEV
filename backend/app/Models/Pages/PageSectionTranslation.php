<?php

namespace App\Models\Pages;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Textes d'une section dans une langue (tous facultatifs : le frontend a des textes par défaut).
 *
 * @property int $id
 * @property int $page_section_id
 * @property string $locale
 * @property string|null $title
 * @property string|null $subtitle
 * @property string|null $body
 * @property string|null $cta_label
 * @property string|null $cta_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation whereCtaLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation whereCtaUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation wherePageSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageSectionTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('page_section_translations')]
#[Fillable(['title', 'subtitle', 'body', 'cta_label', 'cta_url'])]
class PageSectionTranslation extends Model
{
    //
}
