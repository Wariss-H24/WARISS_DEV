<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Valeur d'un réglage traduisible dans une langue.
 *
 * @property int $id
 * @property int $site_setting_id
 * @property string $locale
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SiteSetting $setting
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSettingTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSettingTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSettingTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSettingTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSettingTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSettingTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSettingTranslation whereSiteSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSettingTranslation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteSettingTranslation whereValue($value)
 *
 * @mixin \Eloquent
 */
#[Table('site_setting_translations')]
#[Fillable(['locale', 'value'])]
class SiteSettingTranslation extends Model
{
    /**
     * @return BelongsTo<SiteSetting, $this>
     */
    public function setting(): BelongsTo
    {
        return $this->belongsTo(SiteSetting::class, 'site_setting_id');
    }
}
