<?php

namespace App\Models\Settings;

use App\Enums\NavigationItemType;
use App\Models\Pages\Page;
use App\Support\Concerns\OrderedByPosition;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Settings\NavigationItemFactory;
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
 * Élément de menu (domaine D2) : une seule destination selon son type
 * (page, route du frontend ou URL externe), garantie par navigation_items_destination_check.
 *
 * Le parent doit appartenir au même menu (règle applicative, à valider à l'enregistrement).
 * Une page dépubliée ou supprimée en douceur reste liée : le menu public filtre les pages publiées.
 *
 * @property int $id
 * @property int $navigation_menu_id
 * @property int|null $parent_id
 * @property NavigationItemType $type
 * @property int|null $page_id
 * @property string|null $route_key
 * @property string|null $url
 * @property string|null $icon
 * @property bool $opens_in_new_tab
 * @property bool $is_visible
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, NavigationItem> $children
 * @property-read int|null $children_count
 * @property-read NavigationMenu $menu
 * @property-read Page|null $page
 * @property-read NavigationItem|null $parent
 * @property-read NavigationItemTranslation|null $translation
 * @property-read Collection<int, NavigationItemTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Database\Factories\Settings\NavigationItemFactory factory($count = null, $state = [])
 * @method static Builder<static>|NavigationItem listsTranslations(string $translationField)
 * @method static Builder<static>|NavigationItem newModelQuery()
 * @method static Builder<static>|NavigationItem newQuery()
 * @method static Builder<static>|NavigationItem notTranslatedIn(?string $locale = null)
 * @method static Builder<static>|NavigationItem orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|NavigationItem orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|NavigationItem orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder<static>|NavigationItem ordered()
 * @method static Builder<static>|NavigationItem query()
 * @method static Builder<static>|NavigationItem translated()
 * @method static Builder<static>|NavigationItem translatedIn(?string $locale = null)
 * @method static Builder<static>|NavigationItem visible()
 * @method static Builder<static>|NavigationItem whereCreatedAt($value)
 * @method static Builder<static>|NavigationItem whereIcon($value)
 * @method static Builder<static>|NavigationItem whereId($value)
 * @method static Builder<static>|NavigationItem whereIsVisible($value)
 * @method static Builder<static>|NavigationItem whereNavigationMenuId($value)
 * @method static Builder<static>|NavigationItem whereOpensInNewTab($value)
 * @method static Builder<static>|NavigationItem wherePageId($value)
 * @method static Builder<static>|NavigationItem whereParentId($value)
 * @method static Builder<static>|NavigationItem wherePosition($value)
 * @method static Builder<static>|NavigationItem whereRouteKey($value)
 * @method static Builder<static>|NavigationItem whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder<static>|NavigationItem whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder<static>|NavigationItem whereType($value)
 * @method static Builder<static>|NavigationItem whereUpdatedAt($value)
 * @method static Builder<static>|NavigationItem whereUrl($value)
 * @method static Builder<static>|NavigationItem withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('navigation_items')]
#[Fillable(['navigation_menu_id', 'parent_id', 'type', 'page_id', 'route_key', 'url', 'icon', 'opens_in_new_tab', 'is_visible', 'position'])]
class NavigationItem extends Model implements TranslatableContract
{
    /** @use HasFactory<NavigationItemFactory> */
    use HasFactory, OrderedByPosition, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['label'];

    /**
     * @return BelongsTo<NavigationMenu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(NavigationMenu::class, 'navigation_menu_id');
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
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
            'type' => NavigationItemType::class,
            'opens_in_new_tab' => 'boolean',
            'is_visible' => 'boolean',
            'position' => 'integer',
        ];
    }
}
