<?php

namespace App\Models\Lab;

use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Lab\ExperimentCategoryFactory;
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
 * Catégorie d'expérimentations du laboratoire (domaine D11).
 *
 * @property int $id
 * @property int $position
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Experiment> $experiments
 * @property-read int|null $experiments_count
 * @property-read ExperimentCategoryTranslation|null $translation
 * @property-read Collection<int, ExperimentCategoryTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static Builder<static>|ExperimentCategory active()
 * @method static \Database\Factories\Lab\ExperimentCategoryFactory factory($count = null, $state = [])
 * @method static Builder<static>|ExperimentCategory listsTranslations(string $translationField)
 * @method static Builder<static>|ExperimentCategory newModelQuery()
 * @method static Builder<static>|ExperimentCategory newQuery()
 * @method static Builder<static>|ExperimentCategory notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|ExperimentCategory orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ExperimentCategory orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ExperimentCategory orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|ExperimentCategory ordered()
 * @method static Builder<static>|ExperimentCategory query()
 * @method static Builder<static>|ExperimentCategory translated()
 * @method static Builder<static>|ExperimentCategory translatedIn(?string $locale = null)
 * @method static Builder<static>|ExperimentCategory whereCreatedAt($value)
 * @method static Builder<static>|ExperimentCategory whereId($value)
 * @method static Builder<static>|ExperimentCategory whereIsActive($value)
 * @method static Builder<static>|ExperimentCategory wherePosition($value)
 * @method static Builder<static>|ExperimentCategory whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|ExperimentCategory whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ExperimentCategory whereUpdatedAt($value)
 * @method static Builder<static>|ExperimentCategory withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('experiment_categories')]
#[Fillable(['position', 'is_active'])]
class ExperimentCategory extends Model implements TranslatableContract
{
    /** @use HasFactory<ExperimentCategoryFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['name', 'slug'];

    /**
     * @return HasMany<Experiment, $this>
     */
    public function experiments(): HasMany
    {
        return $this->hasMany(Experiment::class);
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
