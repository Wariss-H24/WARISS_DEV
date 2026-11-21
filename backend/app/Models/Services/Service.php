<?php

namespace App\Models\Services;

use App\Enums\PricingModel;
use App\Enums\PublicationStatus;
use App\Models\Catalog\Technology;
use App\Models\Media\Media;
use App\Models\Pages\PageSectionItem;
use App\Models\Seo\SeoMetadata;
use App\Support\Concerns\FeaturableInSections;
use App\Support\Concerns\HasMediaAttachments;
use App\Support\Concerns\HasPublicationStatus;
use App\Support\Concerns\HasSeo;
use App\Support\Concerns\OrderedByPosition;
use App\Support\Concerns\SearchableTranslations;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Services\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Prestation réellement proposée (domaine D3).
 *
 * Tarif et délai sont facultatifs et ne sont affichés que s'ils sont renseignés et validés ;
 * un service « sur devis » n'a jamais de prix.
 *
 * @property int $id
 * @property int $service_category_id
 * @property int|null $cover_media_id
 * @property string|null $icon
 * @property PricingModel|null $pricing_model
 * @property numeric|null $price_from
 * @property string|null $price_currency
 * @property int|null $delivery_min_days
 * @property int|null $delivery_max_days
 * @property PublicationStatus $status
 * @property Carbon|null $published_at
 * @property bool $is_featured
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Media> $attachedMedia
 * @property-read int|null $attached_media_count
 * @property-read ServiceCategory $category
 * @property-read Media|null $cover
 * @property-read Collection<int, ServiceFeature> $features
 * @property-read int|null $features_count
 * @property-read Collection<int, Media> $gallery
 * @property-read int|null $gallery_count
 * @property-read Collection<int, Technology> $technologies
 * @property-read int|null $technologies_count
 * @property-read ServiceTranslation|null $translation
 * @property-read Collection<int, ServiceTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Services\ServiceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service search(string $term, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service translated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereCoverMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereDeliveryMaxDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereDeliveryMinDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service wherePriceCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service wherePriceFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service wherePricingModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereServiceCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service withTranslation(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service withoutTrashed()
 *
 * @property-read Collection<int, PageSectionItem> $sectionFeatures
 * @property-read int|null $section_features_count
 * @property-read SeoMetadata|null $seo
 *
 * @mixin \Eloquent
 */
#[Table('services')]
#[Fillable([
    'service_category_id', 'cover_media_id', 'icon', 'pricing_model', 'price_from', 'price_currency',
    'delivery_min_days', 'delivery_max_days', 'status', 'published_at', 'is_featured', 'position',
])]
class Service extends Model implements TranslatableContract
{
    /** @use HasFactory<ServiceFactory> */
    use FeaturableInSections, HasFactory, HasMediaAttachments, HasPublicationStatus, HasSeo, OrderedByPosition, SearchableTranslations, SoftDeletes, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'slug', 'summary', 'description', 'target_audience', 'cta_label'];

    /**
     * @return BelongsTo<ServiceCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /**
     * @return HasMany<ServiceFeature, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(ServiceFeature::class);
    }

    /**
     * @return BelongsToMany<Technology, $this>
     */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class);
    }

    /**
     * Indique si un tarif peut être affiché (renseigné, et service non « sur devis »).
     */
    public function hasDisplayablePrice(): bool
    {
        return $this->price_from !== null && $this->pricing_model !== PricingModel::Quote;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pricing_model' => PricingModel::class,
            'price_from' => 'decimal:2',
            'delivery_min_days' => 'integer',
            'delivery_max_days' => 'integer',
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }
}
