<?php

namespace App\Models\Analytics;

use App\Casts\AsJsonObject;
use App\Enums\AnalyticsEventName;
use Database\Factories\Analytics\AnalyticsEventFactory;
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
 * Interaction mesurée sur le site public (domaine D17) : téléchargement du CV, clic,
 * envoi de formulaire, ouverture de démo, partage (D-16)…
 *
 * Sans cookie ni donnée personnelle. Supprimé au-delà de wariss.retention.analytics_raw_days.
 * Nommé AnalyticsEvent pour ne pas être confondu avec les événements Laravel.
 *
 * @property int $id
 * @property Carbon $occurred_at
 * @property AnalyticsEventName $name
 * @property string $path
 * @property string|null $eventable_type
 * @property int|null $eventable_id
 * @property string $session_hash
 * @property array<string, mixed> $properties
 * @property-read Model|null $eventable
 *
 * @method static \Database\Factories\Analytics\AnalyticsEventFactory factory($count = null, $state = [])
 * @method static Builder<static>|AnalyticsEvent newModelQuery()
 * @method static Builder<static>|AnalyticsEvent newQuery()
 * @method static Builder<static>|AnalyticsEvent query()
 * @method static Builder<static>|AnalyticsEvent whereEventableId($value)
 * @method static Builder<static>|AnalyticsEvent whereEventableType($value)
 * @method static Builder<static>|AnalyticsEvent whereId($value)
 * @method static Builder<static>|AnalyticsEvent whereName($value)
 * @method static Builder<static>|AnalyticsEvent whereOccurredAt($value)
 * @method static Builder<static>|AnalyticsEvent wherePath($value)
 * @method static Builder<static>|AnalyticsEvent whereProperties($value)
 * @method static Builder<static>|AnalyticsEvent whereSessionHash($value)
 *
 * @mixin \Eloquent
 */
#[Table('events')]
#[WithoutTimestamps]
#[Fillable(['occurred_at', 'name', 'path', 'eventable_type', 'eventable_id', 'session_hash', 'properties'])]
class AnalyticsEvent extends Model
{
    /** @use HasFactory<AnalyticsEventFactory> */
    use HasFactory, MassPrunable;

    /**
     * @return MorphTo<Model, $this>
     */
    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Événements à supprimer : plus anciens que la durée de conservation des données brutes.
     *
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        $days = (int) config('wariss.retention.analytics_raw_days');

        return static::where('occurred_at', '<', now()->subDays($days));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'name' => AnalyticsEventName::class,
            'properties' => AsJsonObject::class,
        ];
    }
}
