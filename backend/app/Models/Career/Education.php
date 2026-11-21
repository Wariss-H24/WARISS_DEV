<?php

namespace App\Models\Career;

use App\Enums\CompletionStatus;
use App\Enums\DegreeLevel;
use App\Models\Media\Media;
use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Career\EducationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Formation ou diplôme (domaine D8).
 *
 * completion_status indique honnêtement si la formation est en cours, terminée ou interrompue :
 * seul un diplôme « completed » peut être présenté comme obtenu.
 * Le justificatif (proof_media_id) est un média privé, jamais exposé publiquement.
 *
 * @property int $id
 * @property string $institution_name
 * @property string|null $institution_url
 * @property int|null $institution_logo_media_id
 * @property DegreeLevel $degree_level
 * @property CompletionStatus $completion_status
 * @property Carbon|null $started_on
 * @property Carbon|null $ended_on
 * @property string|null $country_code
 * @property int|null $proof_media_id
 * @property bool $is_visible
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Media|null $institutionLogo
 * @property-read Media|null $proof
 * @property-read EducationTranslation|null $translation
 * @property-read Collection<int, EducationTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Career\EducationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Education listsTranslations(string $translationField)
 * @method static Builder<static>|Education mostRecentFirst()
 * @method static Builder<static>|Education newModelQuery()
 * @method static Builder<static>|Education newQuery()
 * @method static Builder<static>|Education notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|Education orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Education orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Education orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|Education ordered()
 * @method static Builder<static>|Education query()
 * @method static Builder<static>|Education translated()
 * @method static Builder<static>|Education translatedIn(?string $locale = null)
 * @method static Builder<static>|Education visible()
 * @method static Builder<static>|Education whereCompletionStatus($value)
 * @method static Builder<static>|Education whereCountryCode($value)
 * @method static Builder<static>|Education whereCreatedAt($value)
 * @method static Builder<static>|Education whereDegreeLevel($value)
 * @method static Builder<static>|Education whereEndedOn($value)
 * @method static Builder<static>|Education whereId($value)
 * @method static Builder<static>|Education whereInstitutionLogoMediaId($value)
 * @method static Builder<static>|Education whereInstitutionName($value)
 * @method static Builder<static>|Education whereInstitutionUrl($value)
 * @method static Builder<static>|Education whereIsVisible($value)
 * @method static Builder<static>|Education wherePosition($value)
 * @method static Builder<static>|Education whereProofMediaId($value)
 * @method static Builder<static>|Education whereStartedOn($value)
 * @method static Builder<static>|Education whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|Education whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Education whereUpdatedAt($value)
 * @method static Builder<static>|Education withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('educations')]
#[Fillable([
    'institution_name', 'institution_url', 'institution_logo_media_id', 'degree_level', 'completion_status',
    'started_on', 'ended_on', 'country_code', 'proof_media_id', 'is_visible', 'position',
])]
class Education extends Model implements TranslatableContract
{
    /** @use HasFactory<EducationFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['degree_title', 'field_of_study', 'location_label', 'description'];

    /**
     * @return BelongsTo<Media, $this>
     */
    public function institutionLogo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'institution_logo_media_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function proof(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'proof_media_id');
    }

    /**
     * Indique si le diplôme peut être présenté comme obtenu.
     */
    public function isObtained(): bool
    {
        return $this->completion_status === CompletionStatus::Completed;
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
     * Ordre d'un CV : formations en cours d'abord, puis de la plus récente à la plus ancienne.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function mostRecentFirst(Builder $query): void
    {
        $query->orderByRaw('CASE WHEN '.$this->qualifyColumn('completion_status')." = 'in_progress' THEN 0 ELSE 1 END")
            ->orderByRaw($this->qualifyColumn('ended_on').' DESC NULLS LAST')
            ->orderByRaw($this->qualifyColumn('started_on').' DESC NULLS LAST')
            ->orderByDesc($this->qualifyColumn('id'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'degree_level' => DegreeLevel::class,
            'completion_status' => CompletionStatus::class,
            'started_on' => 'date',
            'ended_on' => 'date',
            'is_visible' => 'boolean',
            'position' => 'integer',
        ];
    }
}
