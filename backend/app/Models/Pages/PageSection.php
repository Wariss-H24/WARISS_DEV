<?php

namespace App\Models\Pages;

use App\Enums\PageSectionType;
use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Pages\PageSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Section d'une page (domaine D18). Chaque type correspond à un composant React ;
 * config contient seulement des réglages de présentation autorisés (variante, nombre d'éléments…).
 *
 * @property int $id
 * @property int $page_id
 * @property PageSectionType $type
 * @property int $position
 * @property bool $is_active
 * @property array<array-key, mixed> $config
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, PageSectionItem> $items
 * @property-read int|null $items_count
 * @property-read Page|null $page
 * @property-read PageSectionTranslation|null $translation
 * @property-read Collection<int, PageSectionTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static Builder<static>|PageSection active()
 * @method static \Database\Factories\Pages\PageSectionFactory factory($count = null, $state = [])
 * @method static Builder<static>|PageSection listsTranslations(string $translationField)
 * @method static Builder<static>|PageSection newModelQuery()
 * @method static Builder<static>|PageSection newQuery()
 * @method static Builder<static>|PageSection notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|PageSection orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|PageSection orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|PageSection orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|PageSection ordered()
 * @method static Builder<static>|PageSection query()
 * @method static Builder<static>|PageSection translated()
 * @method static Builder<static>|PageSection translatedIn(?string $locale = null)
 * @method static Builder<static>|PageSection whereConfig($value)
 * @method static Builder<static>|PageSection whereCreatedAt($value)
 * @method static Builder<static>|PageSection whereId($value)
 * @method static Builder<static>|PageSection whereIsActive($value)
 * @method static Builder<static>|PageSection wherePageId($value)
 * @method static Builder<static>|PageSection wherePosition($value)
 * @method static Builder<static>|PageSection whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|PageSection whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|PageSection whereType($value)
 * @method static Builder<static>|PageSection whereUpdatedAt($value)
 * @method static Builder<static>|PageSection withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('page_sections')]
#[Fillable(['page_id', 'type', 'position', 'is_active', 'config'])]
class PageSection extends Model implements TranslatableContract
{
    /** @use HasFactory<PageSectionFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['title', 'subtitle', 'body', 'cta_label', 'cta_url'];

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Contenus mis en avant dans la section, dans l'ordre choisi.
     *
     * @return HasMany<PageSectionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PageSectionItem::class)->orderBy('position')->orderBy('id');
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
            'type' => PageSectionType::class,
            'position' => 'integer',
            'is_active' => 'boolean',
            'config' => 'array',
        ];
    }
}
