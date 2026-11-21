<?php

namespace App\Models\Seo;

use App\Casts\AsJsonObject;
use App\Models\Media\Media;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Seo\SeoMetadataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Métadonnées SEO d'un contenu public (domaine D16) : pages, services, projets,
 * études de cas, expérimentations et articles. Une seule fiche par contenu.
 *
 * @property int $id
 * @property string $seoable_type
 * @property int $seoable_id
 * @property bool $robots_index
 * @property bool $robots_follow
 * @property string|null $canonical_url
 * @property int|null $og_image_media_id
 * @property string|null $schema_type
 * @property array<string, mixed>|null $structured_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Media|null $ogImage
 * @property-read Model $seoable
 * @property-read SeoMetadataTranslation|null $translation
 * @property-read Collection<int, SeoMetadataTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Seo\SeoMetadataFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata translated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereCanonicalUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereOgImageMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereRobotsFollow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereRobotsIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereSchemaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereSeoableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereSeoableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereStructuredData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SeoMetadata withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('seo_metadata')]
#[Fillable(['robots_index', 'robots_follow', 'canonical_url', 'og_image_media_id', 'schema_type', 'structured_data'])]
class SeoMetadata extends Model implements TranslatableContract
{
    /** @use HasFactory<SeoMetadataFactory> */
    use HasFactory, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['meta_title', 'meta_description', 'og_title', 'og_description'];

    /**
     * @return MorphTo<Model, $this>
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_media_id');
    }

    /**
     * Valeur de la balise meta robots (ex. « index, follow »).
     */
    public function robotsDirective(): string
    {
        return implode(', ', [
            $this->robots_index ? 'index' : 'noindex',
            $this->robots_follow ? 'follow' : 'nofollow',
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'structured_data' => AsJsonObject::class,
        ];
    }
}
