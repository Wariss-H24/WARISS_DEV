<?php

namespace App\Models\Services;

use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Services\ServiceCategoryFactory;
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
 * Catégorie de services (domaine D3), à définir selon les offres réellement proposées.
 *
 * @property int $id
 * @property string|null $icon
 * @property int $position
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Service> $services
 * @property-read int|null $services_count
 * @property-read ServiceCategoryTranslation|null $translation
 * @property-read Collection<int, ServiceCategoryTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static Builder<static>|ServiceCategory active()
 * @method static \Database\Factories\Services\ServiceCategoryFactory factory($count = null, $state = [])
 * @method static Builder<static>|ServiceCategory listsTranslations(string $translationField)
 * @method static Builder<static>|ServiceCategory newModelQuery()
 * @method static Builder<static>|ServiceCategory newQuery()
 * @method static Builder<static>|ServiceCategory notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|ServiceCategory orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ServiceCategory orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ServiceCategory orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|ServiceCategory ordered()
 * @method static Builder<static>|ServiceCategory query()
 * @method static Builder<static>|ServiceCategory translated()
 * @method static Builder<static>|ServiceCategory translatedIn(?string $locale = null)
 * @method static Builder<static>|ServiceCategory whereCreatedAt($value)
 * @method static Builder<static>|ServiceCategory whereIcon($value)
 * @method static Builder<static>|ServiceCategory whereId($value)
 * @method static Builder<static>|ServiceCategory whereIsActive($value)
 * @method static Builder<static>|ServiceCategory wherePosition($value)
 * @method static Builder<static>|ServiceCategory whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|ServiceCategory whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ServiceCategory whereUpdatedAt($value)
 * @method static Builder<static>|ServiceCategory withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('service_categories')]
#[Fillable(['icon', 'position', 'is_active'])]
class ServiceCategory extends Model implements TranslatableContract
{
    /** @use HasFactory<ServiceCategoryFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['name', 'slug', 'description'];

    /**
     * @return HasMany<Service, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
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
