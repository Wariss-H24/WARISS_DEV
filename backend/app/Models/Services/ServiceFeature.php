<?php

namespace App\Models\Services;

use App\Enums\ServiceFeatureKind;
use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Services\ServiceFeatureFactory;
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
 * Bénéfice, élément inclus ou livrable d'un service.
 *
 * @property int $id
 * @property int $service_id
 * @property ServiceFeatureKind $kind
 * @property string|null $icon
 * @property bool $is_visible
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Service|null $service
 * @property-read ServiceFeatureTranslation|null $translation
 * @property-read Collection<int, ServiceFeatureTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Services\ServiceFeatureFactory factory($count = null, $state = [])
 * @method static Builder<static>|ServiceFeature listsTranslations(string $translationField)
 * @method static Builder<static>|ServiceFeature newModelQuery()
 * @method static Builder<static>|ServiceFeature newQuery()
 * @method static Builder<static>|ServiceFeature notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|ServiceFeature orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ServiceFeature orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ServiceFeature orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|ServiceFeature ordered()
 * @method static Builder<static>|ServiceFeature query()
 * @method static Builder<static>|ServiceFeature translated()
 * @method static Builder<static>|ServiceFeature translatedIn(?string $locale = null)
 * @method static Builder<static>|ServiceFeature visible()
 * @method static Builder<static>|ServiceFeature whereCreatedAt($value)
 * @method static Builder<static>|ServiceFeature whereIcon($value)
 * @method static Builder<static>|ServiceFeature whereId($value)
 * @method static Builder<static>|ServiceFeature whereIsVisible($value)
 * @method static Builder<static>|ServiceFeature whereKind($value)
 * @method static Builder<static>|ServiceFeature wherePosition($value)
 * @method static Builder<static>|ServiceFeature whereServiceId($value)
 * @method static Builder<static>|ServiceFeature whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|ServiceFeature whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|ServiceFeature whereUpdatedAt($value)
 * @method static Builder<static>|ServiceFeature withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('service_features')]
#[Fillable(['service_id', 'kind', 'icon', 'is_visible', 'position'])]
class ServiceFeature extends Model implements TranslatableContract
{
    /** @use HasFactory<ServiceFeatureFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['label', 'description'];

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
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
            'kind' => ServiceFeatureKind::class,
            'is_visible' => 'boolean',
            'position' => 'integer',
        ];
    }
}
