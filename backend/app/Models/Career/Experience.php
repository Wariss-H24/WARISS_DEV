<?php

namespace App\Models\Career;

use App\Enums\EmploymentType;
use App\Models\Catalog\Technology;
use App\Models\Media\Media;
use App\Support\Concerns\AppearsInTimeline;
use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Career\ExperienceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Expérience professionnelle (domaine D8), décrite avec exactitude.
 *
 * Une expérience en cours n'a pas de date de fin ; une expérience terminée en a une,
 * postérieure au début (contrainte experiences_current_dates_check).
 *
 * @property int $id
 * @property string $organization_name
 * @property string|null $organization_url
 * @property int|null $organization_logo_media_id
 * @property EmploymentType $employment_type
 * @property bool $is_remote
 * @property string|null $country_code
 * @property Carbon $started_on
 * @property Carbon|null $ended_on
 * @property bool $is_current
 * @property bool $is_visible
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Media|null $organizationLogo
 * @property-read Collection<int, Technology> $technologies
 * @property-read int|null $technologies_count
 * @property-read ExperienceTranslation|null $translation
 * @property-read Collection<int, ExperienceTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Career\ExperienceFactory factory($count = null, $state = [])
 * @method static Builder<static>|Experience listsTranslations(string $translationField)
 * @method static Builder<static>|Experience mostRecentFirst()
 * @method static Builder<static>|Experience newModelQuery()
 * @method static Builder<static>|Experience newQuery()
 * @method static Builder<static>|Experience notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|Experience onlyTrashed()
 * @method static Builder<static>|Experience orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Experience orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Experience orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|Experience ordered()
 * @method static Builder<static>|Experience query()
 * @method static Builder<static>|Experience translated()
 * @method static Builder<static>|Experience translatedIn(?string $locale = null)
 * @method static Builder<static>|Experience visible()
 * @method static Builder<static>|Experience whereCountryCode($value)
 * @method static Builder<static>|Experience whereCreatedAt($value)
 * @method static Builder<static>|Experience whereDeletedAt($value)
 * @method static Builder<static>|Experience whereEmploymentType($value)
 * @method static Builder<static>|Experience whereEndedOn($value)
 * @method static Builder<static>|Experience whereId($value)
 * @method static Builder<static>|Experience whereIsCurrent($value)
 * @method static Builder<static>|Experience whereIsRemote($value)
 * @method static Builder<static>|Experience whereIsVisible($value)
 * @method static Builder<static>|Experience whereOrganizationLogoMediaId($value)
 * @method static Builder<static>|Experience whereOrganizationName($value)
 * @method static Builder<static>|Experience whereOrganizationUrl($value)
 * @method static Builder<static>|Experience wherePosition($value)
 * @method static Builder<static>|Experience whereStartedOn($value)
 * @method static Builder<static>|Experience whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|Experience whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Experience whereUpdatedAt($value)
 * @method static Builder<static>|Experience withTranslation(?string $locale = null)
 * @method static Builder<static>|Experience withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Experience withoutTrashed()
 *
 * @property-read Collection<int, TimelineEvent> $timelineEvents
 * @property-read int|null $timeline_events_count
 *
 * @mixin \Eloquent
 */
#[Table('experiences')]
#[Fillable([
    'organization_name', 'organization_url', 'organization_logo_media_id', 'employment_type', 'is_remote',
    'country_code', 'started_on', 'ended_on', 'is_current', 'is_visible', 'position',
])]
class Experience extends Model implements TranslatableContract
{
    /** @use HasFactory<ExperienceFactory> */
    use AppearsInTimeline, HasFactory, OrderedByPosition, SoftDeletes, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['job_title', 'location_label', 'summary', 'description', 'achievements'];

    /**
     * @return BelongsTo<Media, $this>
     */
    public function organizationLogo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'organization_logo_media_id');
    }

    /**
     * Technologies réellement utilisées pendant cette expérience.
     *
     * @return BelongsToMany<Technology, $this>
     */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_visible'), true);
    }

    /**
     * Ordre d'un CV : expériences en cours d'abord, puis de la plus récente à la plus ancienne.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function mostRecentFirst(Builder $query): void
    {
        $query->orderByDesc($this->qualifyColumn('is_current'))
            ->orderByDesc($this->qualifyColumn('started_on'))
            ->orderByDesc($this->qualifyColumn('id'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'is_remote' => 'boolean',
            'started_on' => 'date',
            'ended_on' => 'date',
            'is_current' => 'boolean',
            'is_visible' => 'boolean',
            'position' => 'integer',
        ];
    }
}
