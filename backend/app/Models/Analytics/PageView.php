<?php

namespace App\Models\Analytics;

use App\Enums\DeviceType;
use App\Enums\ReferrerType;
use Database\Factories\Analytics\PageViewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Vue d'une page publique (domaine D17), sans cookie ni donnée personnelle (D-16).
 *
 * session_hash est une empreinte quotidienne non réversible : elle permet de compter les
 * visiteurs d'une journée sans pouvoir les identifier. Aucune ligne pour les robots ni
 * pour l'administration. Supprimée au-delà de wariss.retention.analytics_raw_days.
 *
 * @property int $id
 * @property Carbon $viewed_at
 * @property string $path
 * @property string $locale
 * @property string|null $viewable_type
 * @property int|null $viewable_id
 * @property string $session_hash
 * @property string|null $referrer_host
 * @property ReferrerType $referrer_type
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property string|null $utm_campaign
 * @property DeviceType $device_type
 * @property string|null $country_code
 * @property-read Model|null $viewable
 *
 * @method static \Database\Factories\Analytics\PageViewFactory factory($count = null, $state = [])
 * @method static Builder<static>|PageView newModelQuery()
 * @method static Builder<static>|PageView newQuery()
 * @method static Builder<static>|PageView query()
 * @method static Builder<static>|PageView whereCountryCode($value)
 * @method static Builder<static>|PageView whereDeviceType($value)
 * @method static Builder<static>|PageView whereId($value)
 * @method static Builder<static>|PageView whereLocale($value)
 * @method static Builder<static>|PageView wherePath($value)
 * @method static Builder<static>|PageView whereReferrerHost($value)
 * @method static Builder<static>|PageView whereReferrerType($value)
 * @method static Builder<static>|PageView whereSessionHash($value)
 * @method static Builder<static>|PageView whereUtmCampaign($value)
 * @method static Builder<static>|PageView whereUtmMedium($value)
 * @method static Builder<static>|PageView whereUtmSource($value)
 * @method static Builder<static>|PageView whereViewableId($value)
 * @method static Builder<static>|PageView whereViewableType($value)
 * @method static Builder<static>|PageView whereViewedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('page_views')]
#[WithoutTimestamps]
#[Fillable([
    'viewed_at', 'path', 'locale', 'viewable_type', 'viewable_id', 'session_hash', 'referrer_host',
    'referrer_type', 'utm_source', 'utm_medium', 'utm_campaign', 'device_type', 'country_code',
])]
class PageView extends Model
{
    /** @use HasFactory<PageViewFactory> */
    use HasFactory, MassPrunable;

    /**
     * Contenu consulté (projet, article…), s'il s'agit d'une page de contenu.
     *
     * @return MorphTo<Model, $this>
     */
    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Vues à supprimer : plus anciennes que la durée de conservation des données brutes.
     *
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        $days = (int) config('wariss.retention.analytics_raw_days');

        return static::where('viewed_at', '<', now()->subDays($days));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
            'referrer_type' => ReferrerType::class,
            'device_type' => DeviceType::class,
        ];
    }
}
