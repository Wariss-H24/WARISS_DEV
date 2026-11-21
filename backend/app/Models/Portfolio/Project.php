<?php

namespace App\Models\Portfolio;

use App\Enums\ProjectKind;
use App\Enums\ProjectStage;
use App\Enums\PublicationStatus;
use App\Models\Catalog\Skill;
use App\Models\Catalog\Technology;
use App\Models\Media\Media;
use App\Support\Concerns\HasMediaAttachments;
use App\Support\Concerns\HasPublicationStatus;
use App\Support\Concerns\OrderedByPosition;
use App\Support\Concerns\SearchableTranslations;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Portfolio\ProjectFactory;
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
 * Projet du portfolio (domaine D6).
 *
 * Décrit honnêtement : rôle réellement exercé, fonctionnalités développées, technologies utilisées.
 * Un projet confidentiel (is_confidential) ne doit jamais exposer publiquement son client ni ses liens :
 * la ressource publique de l'API applique cette règle.
 *
 * @property int $id
 * @property int $project_category_id
 * @property int|null $cover_media_id
 * @property ProjectKind $kind
 * @property ProjectStage $development_stage
 * @property string|null $client_name
 * @property Carbon|null $started_on
 * @property Carbon|null $ended_on
 * @property string|null $live_url
 * @property string|null $repository_url
 * @property string|null $demo_url
 * @property bool $is_confidential
 * @property PublicationStatus $status
 * @property Carbon|null $published_at
 * @property bool $is_featured
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Media> $attachedMedia
 * @property-read int|null $attached_media_count
 * @property-read ProjectCategory $category
 * @property-read Media|null $cover
 * @property-read Collection<int, ProjectFeature> $features
 * @property-read int|null $features_count
 * @property-read Collection<int, Media> $gallery
 * @property-read int|null $gallery_count
 * @property-read Collection<int, Skill> $skills
 * @property-read int|null $skills_count
 * @property-read Collection<int, Technology> $technologies
 * @property-read int|null $technologies_count
 * @property-read ProjectTranslation|null $translation
 * @property-read Collection<int, ProjectTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Portfolio\ProjectFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project search(string $term, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project translated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereCoverMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereDemoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereDevelopmentStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereEndedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereIsConfidential($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereKind($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereLiveUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereProjectCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereRepositoryUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereStartedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project withTranslation(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project withoutTrashed()
 *
 * @property-read Collection<int, CaseStudy> $caseStudies
 * @property-read int|null $case_studies_count
 *
 * @mixin \Eloquent
 */
#[Table('projects')]
#[Fillable([
    'project_category_id', 'cover_media_id', 'kind', 'development_stage', 'client_name',
    'started_on', 'ended_on', 'live_url', 'repository_url', 'demo_url', 'is_confidential',
    'status', 'published_at', 'is_featured', 'position',
])]
class Project extends Model implements TranslatableContract
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasMediaAttachments, HasPublicationStatus, OrderedByPosition, SearchableTranslations, SoftDeletes, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'slug', 'summary', 'description', 'context', 'role', 'responsibilities'];

    /**
     * @return BelongsTo<ProjectCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /**
     * @return HasMany<ProjectFeature, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(ProjectFeature::class);
    }

    /**
     * @return BelongsToMany<Technology, $this>
     */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class);
    }

    /**
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class);
    }

    /**
     * @return HasMany<CaseStudy, $this>
     */
    public function caseStudies(): HasMany
    {
        return $this->hasMany(CaseStudy::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ProjectKind::class,
            'development_stage' => ProjectStage::class,
            'started_on' => 'date',
            'ended_on' => 'date',
            'is_confidential' => 'boolean',
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }
}
