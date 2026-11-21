<?php

namespace App\Models\Catalog;

use App\Enums\TechnologyProficiency;
use App\Models\Media\Media;
use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Catalog\TechnologyFactory;
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
 * Technologie du catalogue central (domaine D4) : enregistrée une seule fois,
 * puis réutilisée par les compétences, projets, services, expériences… via des pivots.
 *
 * Le nom est une marque (React, PostgreSQL) : il n'est pas traduit. Seule la description l'est.
 *
 * @property int $id
 * @property int $technology_category_id
 * @property string $name
 * @property string $slug
 * @property int|null $icon_media_id
 * @property string|null $icon_key
 * @property string|null $brand_color
 * @property string|null $official_url
 * @property TechnologyProficiency|null $proficiency
 * @property bool $is_visible
 * @property bool $is_featured
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TechnologyCategory $category
 * @property-read Media|null $icon
 * @property-read Collection<int, Skill> $skills
 * @property-read int|null $skills_count
 * @property-read TechnologyTranslation|null $translation
 * @property-read Collection<int, TechnologyTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Catalog\TechnologyFactory factory($count = null, $state = [])
 * @method static Builder<static>|Technology listsTranslations(string $translationField)
 * @method static Builder<static>|Technology newModelQuery()
 * @method static Builder<static>|Technology newQuery()
 * @method static Builder<static>|Technology notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|Technology orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Technology orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Technology orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|Technology ordered()
 * @method static Builder<static>|Technology query()
 * @method static Builder<static>|Technology translated()
 * @method static Builder<static>|Technology translatedIn(?string $locale = null)
 * @method static Builder<static>|Technology visible()
 * @method static Builder<static>|Technology whereBrandColor($value)
 * @method static Builder<static>|Technology whereCreatedAt($value)
 * @method static Builder<static>|Technology whereIconKey($value)
 * @method static Builder<static>|Technology whereIconMediaId($value)
 * @method static Builder<static>|Technology whereId($value)
 * @method static Builder<static>|Technology whereIsFeatured($value)
 * @method static Builder<static>|Technology whereIsVisible($value)
 * @method static Builder<static>|Technology whereName($value)
 * @method static Builder<static>|Technology whereOfficialUrl($value)
 * @method static Builder<static>|Technology wherePosition($value)
 * @method static Builder<static>|Technology whereProficiency($value)
 * @method static Builder<static>|Technology whereSlug($value)
 * @method static Builder<static>|Technology whereTechnologyCategoryId($value)
 * @method static Builder<static>|Technology whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|Technology whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Technology whereUpdatedAt($value)
 * @method static Builder<static>|Technology withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('technologies')]
#[Fillable([
    'technology_category_id', 'name', 'slug', 'icon_media_id', 'icon_key', 'brand_color',
    'official_url', 'proficiency', 'is_visible', 'is_featured', 'position',
])]
class Technology extends Model implements TranslatableContract
{
    /** @use HasFactory<TechnologyFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['description'];

    /**
     * @return BelongsTo<TechnologyCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TechnologyCategory::class, 'technology_category_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function icon(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'icon_media_id');
    }

    /**
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class);
    }

    /**
     * Technologies affichables sur le site public.
     *
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
            'proficiency' => TechnologyProficiency::class,
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }
}
