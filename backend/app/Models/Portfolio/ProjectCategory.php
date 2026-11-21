<?php

namespace App\Models\Portfolio;

use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Portfolio\ProjectCategoryFactory;
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
 * Catégorie de projets : application web, e-commerce, outil de gestion… (domaine D6).
 *
 * Un projet appartient à une seule catégorie (décision validée).
 *
 * @property int $id
 * @property int $position
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Project> $projects
 * @property-read int|null $projects_count
 * @property-read ProjectCategoryTranslation|null $translation
 * @property-read Collection<int, ProjectCategoryTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static Builder<static>|ProjectCategory active()
 * @method static \Database\Factories\Portfolio\ProjectCategoryFactory factory($count = null, $state = [])
 * @method static Builder<static>|ProjectCategory listsTranslations(string $translationField)
 * @method static Builder<static>|ProjectCategory newModelQuery()
 * @method static Builder<static>|ProjectCategory newQuery()
 * @method static Builder<static>|ProjectCategory notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|ProjectCategory orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ProjectCategory orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ProjectCategory orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|ProjectCategory ordered()
 * @method static Builder<static>|ProjectCategory query()
 * @method static Builder<static>|ProjectCategory translated()
 * @method static Builder<static>|ProjectCategory translatedIn(?string $locale = null)
 * @method static Builder<static>|ProjectCategory whereCreatedAt($value)
 * @method static Builder<static>|ProjectCategory whereId($value)
 * @method static Builder<static>|ProjectCategory whereIsActive($value)
 * @method static Builder<static>|ProjectCategory wherePosition($value)
 * @method static Builder<static>|ProjectCategory whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|ProjectCategory whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ProjectCategory whereUpdatedAt($value)
 * @method static Builder<static>|ProjectCategory withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('project_categories')]
#[Fillable(['position', 'is_active'])]
class ProjectCategory extends Model implements TranslatableContract
{
    /** @use HasFactory<ProjectCategoryFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['name', 'slug'];

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
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
