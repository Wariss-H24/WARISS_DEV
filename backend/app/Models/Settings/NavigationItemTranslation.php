<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Libellé d'un élément de menu dans une langue.
 *
 * @property int $id
 * @property int $navigation_item_id
 * @property string $locale
 * @property string $label
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItemTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItemTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItemTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItemTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItemTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItemTranslation whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItemTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItemTranslation whereNavigationItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItemTranslation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('navigation_item_translations')]
#[Fillable(['label'])]
class NavigationItemTranslation extends Model
{
    //
}
