<?php

namespace App\Models\Catalog;

use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Catalog\SkillFactory;
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
 * Compétence (domaine D5) : ce que le propriétaire sait réaliser, par exemple
 * « Conception d'API REST ». Les technologies utilisées pour la réaliser sont liées par pivot.
 *
 * proficiency (1 à 5) et years_of_experience ne sont affichés que s'ils sont renseignés et justifiables.
 *
 * @property int $id
 * @property int $skill_category_id
 * @property string $slug
 * @property int|null $proficiency
 * @property numeric|null $years_of_experience
 * @property bool $is_visible
 * @property bool $is_featured
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SkillCategory $category
 * @property-read Collection<int, Technology> $technologies
 * @property-read int|null $technologies_count
 * @property-read SkillTranslation|null $translation
 * @property-read Collection<int, SkillTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Catalog\SkillFactory factory($count = null, $state = [])
 * @method static Builder<static>|Skill listsTranslations(string $translationField)
 * @method static Builder<static>|Skill newModelQuery()
 * @method static Builder<static>|Skill newQuery()
 * @method static Builder<static>|Skill notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|Skill orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Skill orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Skill orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|Skill ordered()
 * @method static Builder<static>|Skill query()
 * @method static Builder<static>|Skill translated()
 * @method static Builder<static>|Skill translatedIn(?string $locale = null)
 * @method static Builder<static>|Skill visible()
 * @method static Builder<static>|Skill whereCreatedAt($value)
 * @method static Builder<static>|Skill whereId($value)
 * @method static Builder<static>|Skill whereIsFeatured($value)
 * @method static Builder<static>|Skill whereIsVisible($value)
 * @method static Builder<static>|Skill wherePosition($value)
 * @method static Builder<static>|Skill whereProficiency($value)
 * @method static Builder<static>|Skill whereSkillCategoryId($value)
 * @method static Builder<static>|Skill whereSlug($value)
 * @method static Builder<static>|Skill whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|Skill whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|Skill whereUpdatedAt($value)
 * @method static Builder<static>|Skill whereYearsOfExperience($value)
 * @method static Builder<static>|Skill withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('skills')]
#[Fillable(['skill_category_id', 'slug', 'proficiency', 'years_of_experience', 'is_visible', 'is_featured', 'position'])]
class Skill extends Model implements TranslatableContract
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['name', 'description'];

    /**
     * @return BelongsTo<SkillCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class, 'skill_category_id');
    }

    /**
     * @return BelongsToMany<Technology, $this>
     */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class);
    }

    /**
     * Compétences affichables sur le site public.
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
            'proficiency' => 'integer',
            'years_of_experience' => 'decimal:1',
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }
}
