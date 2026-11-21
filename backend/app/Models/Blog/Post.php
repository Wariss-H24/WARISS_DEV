<?php

namespace App\Models\Blog;

use App\Enums\PostType;
use App\Enums\PublicationStatus;
use App\Models\Media\Media;
use App\Models\Pages\PageSectionItem;
use App\Models\Seo\SeoMetadata;
use App\Models\User;
use App\Support\Concerns\FeaturableInSections;
use App\Support\Concerns\HasMediaAttachments;
use App\Support\Concerns\HasPublicationStatus;
use App\Support\Concerns\HasSeo;
use App\Support\Concerns\SearchableTranslations;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Blog\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Article du blog (domaine D12) : article, tutoriel, guide, retour d'expérience…
 *
 * Le contenu est du Markdown, nettoyé au rendu. L'auteur est un compte d'administration ;
 * il peut disparaître (SET NULL) sans supprimer l'article. Pas de commentaires publics.
 *
 * @property int $id
 * @property int|null $author_id
 * @property int $post_category_id
 * @property int|null $cover_media_id
 * @property PostType $type
 * @property int|null $reading_time_minutes
 * @property PublicationStatus $status
 * @property Carbon|null $published_at
 * @property bool $is_featured
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Media> $attachedMedia
 * @property-read int|null $attached_media_count
 * @property-read User|null $author
 * @property-read PostCategory $category
 * @property-read Media|null $cover
 * @property-read Collection<int, Media> $gallery
 * @property-read int|null $gallery_count
 * @property-read Collection<int, Tag> $tags
 * @property-read int|null $tags_count
 * @property-read PostTranslation|null $translation
 * @property-read Collection<int, PostTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Blog\PostFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post search(string $term, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post translated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereCoverMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post wherePostCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereReadingTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post withTranslation(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post withoutTrashed()
 *
 * @property-read Collection<int, PageSectionItem> $sectionFeatures
 * @property-read int|null $section_features_count
 * @property-read SeoMetadata|null $seo
 *
 * @mixin \Eloquent
 */
#[Table('posts')]
#[Fillable([
    'post_category_id', 'cover_media_id', 'type', 'reading_time_minutes',
    'status', 'published_at', 'is_featured', 'position',
])]
class Post extends Model implements TranslatableContract
{
    /** @use HasFactory<PostFactory> */
    use FeaturableInSections, HasFactory, HasMediaAttachments, HasPublicationStatus, HasSeo, SearchableTranslations, SoftDeletes, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'slug', 'excerpt', 'content'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'reading_time_minutes' => 'integer',
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }
}
