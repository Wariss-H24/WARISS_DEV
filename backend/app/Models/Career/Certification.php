<?php

namespace App\Models\Career;

use App\Models\Catalog\Technology;
use App\Models\Media\Media;
use App\Models\Pages\PageSectionItem;
use App\Support\Concerns\AppearsInTimeline;
use App\Support\Concerns\FeaturableInSections;
use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Career\CertificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Certification ou attestation (domaine D9), vérifiable par son identifiant et son lien.
 *
 * @property int $id
 * @property string $issuer_name
 * @property string|null $issuer_url
 * @property string|null $credential_id
 * @property string|null $verification_url
 * @property Carbon $issued_on
 * @property Carbon|null $expires_on
 * @property int|null $certificate_media_id
 * @property bool $is_visible
 * @property bool $is_featured
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Media|null $certificate
 * @property-read Collection<int, Technology> $technologies
 * @property-read int|null $technologies_count
 * @property-read CertificationTranslation|null $translation
 * @property-read Collection<int, CertificationTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Career\CertificationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Certification listsTranslations(string $translationField)
 * @method static Builder<static>|Certification newModelQuery()
 * @method static Builder<static>|Certification newQuery()
 * @method static Builder<static>|Certification notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|Certification orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Certification orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Certification orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|Certification ordered()
 * @method static Builder<static>|Certification query()
 * @method static Builder<static>|Certification translated()
 * @method static Builder<static>|Certification translatedIn(?string $locale = null)
 * @method static Builder<static>|Certification visible()
 * @method static Builder<static>|Certification whereCertificateMediaId($value)
 * @method static Builder<static>|Certification whereCreatedAt($value)
 * @method static Builder<static>|Certification whereCredentialId($value)
 * @method static Builder<static>|Certification whereExpiresOn($value)
 * @method static Builder<static>|Certification whereId($value)
 * @method static Builder<static>|Certification whereIsFeatured($value)
 * @method static Builder<static>|Certification whereIsVisible($value)
 * @method static Builder<static>|Certification whereIssuedOn($value)
 * @method static Builder<static>|Certification whereIssuerName($value)
 * @method static Builder<static>|Certification whereIssuerUrl($value)
 * @method static Builder<static>|Certification wherePosition($value)
 * @method static Builder<static>|Certification whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|Certification whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Certification whereUpdatedAt($value)
 * @method static Builder<static>|Certification whereVerificationUrl($value)
 * @method static Builder<static>|Certification withTranslation(?string $locale = null)
 *
 * @property-read Collection<int, TimelineEvent> $timelineEvents
 * @property-read int|null $timeline_events_count
 * @property-read Collection<int, PageSectionItem> $sectionFeatures
 * @property-read int|null $section_features_count
 *
 * @mixin \Eloquent
 */
#[Table('certifications')]
#[Fillable([
    'issuer_name', 'issuer_url', 'credential_id', 'verification_url', 'issued_on', 'expires_on',
    'certificate_media_id', 'is_visible', 'is_featured', 'position',
])]
class Certification extends Model implements TranslatableContract
{
    /** @use HasFactory<CertificationFactory> */
    use AppearsInTimeline, FeaturableInSections, HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'description'];

    /**
     * @return BelongsTo<Media, $this>
     */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'certificate_media_id');
    }

    /**
     * @return BelongsToMany<Technology, $this>
     */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class);
    }

    /**
     * Indique si la certification a expiré à la date du jour.
     */
    public function isExpired(): bool
    {
        return $this->expires_on !== null && $this->expires_on->lt(today());
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issued_on' => 'date',
            'expires_on' => 'date',
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }
}
