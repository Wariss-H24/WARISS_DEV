<?php

namespace App\Models\Communication;

use App\Enums\TestimonialStatus;
use App\Models\Media\Media;
use App\Models\Portfolio\Project;
use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Communication\TestimonialFactory;
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
 * Témoignage d'un client ou d'un collaborateur (domaine D13), authentique.
 *
 * Publié seulement s'il est approuvé, avec le consentement de son auteur, et visible.
 * Le statut et le consentement ne sont pas assignables en masse : ils sont fixés par l'administration.
 *
 * @property int $id
 * @property string $author_name
 * @property string|null $author_company
 * @property int|null $photo_media_id
 * @property int|null $project_id
 * @property Carbon|null $given_on
 * @property bool $consent_given
 * @property TestimonialStatus $status
 * @property bool $is_visible
 * @property bool $is_featured
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Media|null $photo
 * @property-read Project|null $project
 * @property-read TestimonialTranslation|null $translation
 * @property-read Collection<int, TestimonialTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Communication\TestimonialFactory factory($count = null, $state = [])
 * @method static Builder<static>|Testimonial listsTranslations(string $translationField)
 * @method static Builder<static>|Testimonial newModelQuery()
 * @method static Builder<static>|Testimonial newQuery()
 * @method static Builder<static>|Testimonial notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|Testimonial orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Testimonial orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Testimonial orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|Testimonial ordered()
 * @method static Builder<static>|Testimonial publishable()
 * @method static Builder<static>|Testimonial query()
 * @method static Builder<static>|Testimonial translated()
 * @method static Builder<static>|Testimonial translatedIn(?string $locale = null)
 * @method static Builder<static>|Testimonial whereAuthorCompany($value)
 * @method static Builder<static>|Testimonial whereAuthorName($value)
 * @method static Builder<static>|Testimonial whereConsentGiven($value)
 * @method static Builder<static>|Testimonial whereCreatedAt($value)
 * @method static Builder<static>|Testimonial whereGivenOn($value)
 * @method static Builder<static>|Testimonial whereId($value)
 * @method static Builder<static>|Testimonial whereIsFeatured($value)
 * @method static Builder<static>|Testimonial whereIsVisible($value)
 * @method static Builder<static>|Testimonial wherePhotoMediaId($value)
 * @method static Builder<static>|Testimonial wherePosition($value)
 * @method static Builder<static>|Testimonial whereProjectId($value)
 * @method static Builder<static>|Testimonial whereStatus($value)
 * @method static Builder<static>|Testimonial whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|Testimonial whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Testimonial whereUpdatedAt($value)
 * @method static Builder<static>|Testimonial withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('testimonials')]
#[Fillable(['author_name', 'author_company', 'photo_media_id', 'project_id', 'given_on', 'is_visible', 'is_featured', 'position'])]
class Testimonial extends Model implements TranslatableContract
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['quote', 'author_role'];

    /**
     * @return BelongsTo<Media, $this>
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_media_id');
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Témoignages publiables : approuvés, avec consentement, et visibles.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function publishable(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), TestimonialStatus::Approved->value)
            ->where($this->qualifyColumn('consent_given'), true)
            ->where($this->qualifyColumn('is_visible'), true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'given_on' => 'date',
            'consent_given' => 'boolean',
            'status' => TestimonialStatus::class,
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }
}
