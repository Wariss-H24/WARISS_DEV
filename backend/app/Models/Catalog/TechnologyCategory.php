<?php

namespace App\Models\Catalog;

use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Catalog\TechnologyCategoryFactory;
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
 * Catégorie de technologies : langages, frameworks, bases de données… (domaine D4).
 *
 * @property int $id
 * @property int $position
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Technology> $technologies
 * @property-read int|null $technologies_count
 * @property-read TechnologyCategoryTranslation|null $translation
 * @property-read Collection<int, TechnologyCategoryTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static Builder<static>|TechnologyCategory active()
 * @method static \Database\Factories\Catalog\TechnologyCategoryFactory factory($count = null, $state = [])
 * @method static Builder<static>|TechnologyCategory listsTranslations(string $translationField)
 * @method static Builder<static>|TechnologyCategory newModelQuery()
 * @method static Builder<static>|TechnologyCategory newQuery()
 * @method static Builder<static>|TechnologyCategory notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|TechnologyCategory orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|TechnologyCategory orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|TechnologyCategory orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|TechnologyCategory ordered()
 * @method static Builder<static>|TechnologyCategory query()
 * @method static Builder<static>|TechnologyCategory translated()
 * @method static Builder<static>|TechnologyCategory translatedIn(?string $locale = null)
 * @method static Builder<static>|TechnologyCategory whereCreatedAt($value)
 * @method static Builder<static>|TechnologyCategory whereId($value)
 * @method static Builder<static>|TechnologyCategory whereIsActive($value)
 * @method static Builder<static>|TechnologyCategory wherePosition($value)
 * @method static Builder<static>|TechnologyCategory whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|TechnologyCategory whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|TechnologyCategory whereUpdatedAt($value)
 * @method static Builder<static>|TechnologyCategory withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('technology_categories')]
#[Fillable(['position', 'is_active'])]
class TechnologyCategory extends Model implements TranslatableContract
{
    /** @use HasFactory<TechnologyCategoryFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['name', 'slug'];

    /**
     * @return HasMany<Technology, $this>
     */
    public function technologies(): HasMany
    {
        return $this->hasMany(Technology::class);
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
