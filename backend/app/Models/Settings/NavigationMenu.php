<?php

namespace App\Models\Settings;

use App\Enums\NavigationLocation;
use Database\Factories\Settings\NavigationMenuFactory;
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
 * Menu du site (principal, pied de page, légal…). Un emplacement n'accueille qu'un menu.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property NavigationLocation|null $location
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, NavigationItem> $items
 * @property-read int|null $items_count
 * @property-read Collection<int, NavigationItem> $rootItems
 * @property-read int|null $root_items_count
 *
 * @method static Builder<static>|NavigationMenu active()
 * @method static \Database\Factories\Settings\NavigationMenuFactory factory($count = null, $state = [])
 * @method static Builder<static>|NavigationMenu newModelQuery()
 * @method static Builder<static>|NavigationMenu newQuery()
 * @method static Builder<static>|NavigationMenu query()
 * @method static Builder<static>|NavigationMenu whereCreatedAt($value)
 * @method static Builder<static>|NavigationMenu whereId($value)
 * @method static Builder<static>|NavigationMenu whereIsActive($value)
 * @method static Builder<static>|NavigationMenu whereLocation($value)
 * @method static Builder<static>|NavigationMenu whereName($value)
 * @method static Builder<static>|NavigationMenu whereSlug($value)
 * @method static Builder<static>|NavigationMenu whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('navigation_menus')]
#[Fillable(['name', 'slug', 'location', 'is_active'])]
class NavigationMenu extends Model
{
    /** @use HasFactory<NavigationMenuFactory> */
    use HasFactory;

    /**
     * Tous les éléments du menu, à tous les niveaux.
     *
     * @return HasMany<NavigationItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(NavigationItem::class);
    }

    /**
     * Éléments de premier niveau, dans l'ordre d'affichage.
     *
     * @return HasMany<NavigationItem, $this>
     */
    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id')->orderBy('position')->orderBy('id');
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
            'location' => NavigationLocation::class,
            'is_active' => 'boolean',
        ];
    }
}
