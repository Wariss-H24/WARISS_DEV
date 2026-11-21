<?php

namespace App\Models\Lab;

use App\Enums\ExperimentKind;
use App\Enums\PublicationStatus;
use App\Models\Catalog\Technology;
use App\Models\Media\Media;
use App\Support\Concerns\HasMediaAttachments;
use App\Support\Concerns\HasPublicationStatus;
use App\Support\Concerns\OrderedByPosition;
use App\Support\Concerns\SearchableTranslations;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Lab\ExperimentFactory;
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
 * Expérimentation du laboratoire (domaine D11) : démonstration, prototype, mini-outil…
 *
 * Aucun code n'est stocké ni exécuté : une démonstration intégrée est désignée par
 * demo_component_key, qui doit figurer dans la liste blanche des composants du frontend ;
 * les démonstrations externes (demo_url) s'affichent dans un iframe isolé (sandbox).
 *
 * @property int $id
 * @property int $experiment_category_id
 * @property int|null $cover_media_id
 * @property ExperimentKind $kind
 * @property string|null $demo_url
 * @property string|null $repository_url
 * @property string|null $video_url
 * @property string|null $demo_component_key
 * @property Carbon|null $experimented_on
 * @property PublicationStatus $status
 * @property Carbon|null $published_at
 * @property bool $is_featured
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Media> $attachedMedia
 * @property-read int|null $attached_media_count
 * @property-read ExperimentCategory $category
 * @property-read Media|null $cover
 * @property-read Collection<int, Media> $gallery
 * @property-read int|null $gallery_count
 * @property-read Collection<int, Technology> $technologies
 * @property-read int|null $technologies_count
 * @property-read ExperimentTranslation|null $translation
 * @property-read Collection<int, ExperimentTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Lab\ExperimentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment search(string $term, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment translated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereCoverMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereDemoComponentKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereDemoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereExperimentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereExperimentedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereKind($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereRepositoryUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment whereVideoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment withTranslation(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experiment withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[Table('experiments')]
#[Fillable([
    'experiment_category_id', 'cover_media_id', 'kind', 'demo_url', 'repository_url', 'video_url',
    'demo_component_key', 'experimented_on', 'status', 'published_at', 'is_featured', 'position',
])]
class Experiment extends Model implements TranslatableContract
{
    /** @use HasFactory<ExperimentFactory> */
    use HasFactory, HasMediaAttachments, HasPublicationStatus, OrderedByPosition, SearchableTranslations, SoftDeletes, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'slug', 'summary', 'description', 'objective', 'technical_notes'];

    /**
     * @return BelongsTo<ExperimentCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExperimentCategory::class, 'experiment_category_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /**
     * @return BelongsToMany<Technology, $this>
     */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ExperimentKind::class,
            'experimented_on' => 'date',
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }
}
