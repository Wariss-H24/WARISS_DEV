<?php

namespace App\Models\Portfolio;

use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Portfolio\ProjectFeatureFactory;
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
 * Fonctionnalité ou module d'un projet, réellement développé.
 *
 * @property int $id
 * @property int $project_id
 * @property bool $is_visible
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Project|null $project
 * @property-read ProjectFeatureTranslation|null $translation
 * @property-read Collection<int, ProjectFeatureTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Portfolio\ProjectFeatureFactory factory($count = null, $state = [])
 * @method static Builder<static>|ProjectFeature listsTranslations(string $translationField)
 * @method static Builder<static>|ProjectFeature newModelQuery()
 * @method static Builder<static>|ProjectFeature newQuery()
 * @method static Builder<static>|ProjectFeature notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|ProjectFeature orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ProjectFeature orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ProjectFeature orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|ProjectFeature ordered()
 * @method static Builder<static>|ProjectFeature query()
 * @method static Builder<static>|ProjectFeature translated()
 * @method static Builder<static>|ProjectFeature translatedIn(?string $locale = null)
 * @method static Builder<static>|ProjectFeature visible()
 * @method static Builder<static>|ProjectFeature whereCreatedAt($value)
 * @method static Builder<static>|ProjectFeature whereId($value)
 * @method static Builder<static>|ProjectFeature whereIsVisible($value)
 * @method static Builder<static>|ProjectFeature wherePosition($value)
 * @method static Builder<static>|ProjectFeature whereProjectId($value)
 * @method static Builder<static>|ProjectFeature whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|ProjectFeature whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ProjectFeature whereUpdatedAt($value)
 * @method static Builder<static>|ProjectFeature withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('project_features')]
#[Fillable(['project_id', 'is_visible', 'position'])]
class ProjectFeature extends Model implements TranslatableContract
{
    /** @use HasFactory<ProjectFeatureFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'description'];

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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
            'is_visible' => 'boolean',
            'position' => 'integer',
        ];
    }
}
