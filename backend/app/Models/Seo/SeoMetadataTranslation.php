<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Titre, description et données de partage social d'un contenu dans une langue.
 *
 * @property int $id
 * @property int $seo_metadata_id
 * @property string $locale
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $og_title
 * @property string|null $og_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation whereOgDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation whereOgTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation whereSeoMetadataId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadataTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('seo_metadata_translations')]
#[Fillable(['meta_title', 'meta_description', 'og_title', 'og_description'])]
class SeoMetadataTranslation extends Model
{
    //
}
