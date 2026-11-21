<?php

namespace App\Models\Media;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Textes d'un média dans une langue : texte alternatif, légende, description.
 *
 * @property int $id
 * @property int $media_id
 * @property string $locale
 * @property string|null $alt_text
 * @property string|null $caption
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation whereCaption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('media_translations')]
#[Fillable(['alt_text', 'caption', 'description'])]
class MediaTranslation extends Model
{
    //
}
