<?php

namespace App\Models\Catalog;

use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Catalog\SkillCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Catégorie de compétences : frontend, backend, conception… (domaine D5).
 *
 * @property int $id
 * @property int $position
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Skill> $skills
 * @property-read int|null $skills_count
 * @property-read SkillCategoryTranslation|null $translation
 * @property-read Collection<int, SkillCategoryTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static Builder<static>|SkillCategory active()
 * @method static \Database\Factories\Catalog\SkillCategoryFactory factory($count = null, $state = [])
 * @method static Builder<static>|SkillCategory listsTranslations(string $translationField)
 * @method static Builder<static>|SkillCategory newModelQuery()
 * @method static Builder<static>|SkillCategory newQuery()
 * @method static Builder<static>|SkillCategory notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|SkillCategory orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|SkillCategory orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|SkillCategory orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|SkillCategory ordered()
 * @method static Builder<static>|SkillCategory query()
 * @method static Builder<static>|SkillCategory translated()
 * @method static Builder<static>|SkillCategory translatedIn(?string $locale = null)
 * @method static Builder<static>|SkillCategory whereCreatedAt($value)
 * @method static Builder<static>|SkillCategory whereId($value)
 * @method static Builder<static>|SkillCategory whereIsActive($value)
 * @method static Builder<static>|SkillCategory wherePosition($value)
 * @method static Builder<static>|SkillCategory whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|SkillCategory whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|SkillCategory whereUpdatedAt($value)
 * @method static Builder<static>|SkillCategory withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('skill_categories')]
#[Fillable(['position', 'is_active'])]
class SkillCategory extends Model implements TranslatableContract
{
    /** @use HasFactory<SkillCategoryFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['name', 'slug'];

    /**
     * @return HasMany<Skill, $this>
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_active'), true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
