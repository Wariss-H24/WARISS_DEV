<?php

namespace App\Models\Portfolio;

use App\Enums\PublicationStatus;
use App\Models\Catalog\Technology;
use App\Models\Media\Media;
use App\Models\Seo\SeoMetadata;
use App\Support\Concerns\HasMediaAttachments;
use App\Support\Concerns\HasPublicationStatus;
use App\Support\Concerns\HasSeo;
use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Portfolio\CaseStudyFactory;
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
 * Étude de cas (domaine D7) : problème, démarche, choix techniques, résultats réellement observés.
 *
 * Un projet peut en avoir zéro ou plusieurs ; une étude survit à la suppression de son projet.
 *
 * @property int $id
 * @property int|null $project_id
 * @property int|null $cover_media_id
 * @property Carbon|null $started_on
 * @property Carbon|null $ended_on
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
 * @property-read Media|null $cover
 * @property-read Collection<int, Media> $gallery
 * @property-read int|null $gallery_count
 * @property-read Project|null $project
 * @property-read Collection<int, CaseStudySection> $sections
 * @property-read int|null $sections_count
 * @property-read Collection<int, Technology> $technologies
 * @property-read int|null $technologies_count
 * @property-read CaseStudyTranslation|null $translation
 * @property-read Collection<int, CaseStudyTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Portfolio\CaseStudyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy ordered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy translated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereCoverMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereEndedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereReadingTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereStartedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy withTranslation(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CaseStudy withoutTrashed()
 *
 * @property-read SeoMetadata|null $seo
 *
 * @mixin \Eloquent
 */
#[Table('case_studies')]
#[Fillable([
    'project_id', 'cover_media_id', 'started_on', 'ended_on', 'reading_time_minutes',
    'status', 'published_at', 'is_featured', 'position',
])]
class CaseStudy extends Model implements TranslatableContract
{
    /** @use HasFactory<CaseStudyFactory> */
    use HasFactory, HasMediaAttachments, HasPublicationStatus, HasSeo, OrderedByPosition, SoftDeletes, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'slug', 'summary'];

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /**
     * Sections dans l'ordre de lecture.
     *
     * @return HasMany<CaseStudySection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(CaseStudySection::class)->orderBy('position')->orderBy('id');
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
            'started_on' => 'date',
            'ended_on' => 'date',
            'reading_time_minutes' => 'integer',
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }
}
