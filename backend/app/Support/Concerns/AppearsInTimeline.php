<?php

namespace App\Support\Concerns;

use App\Models\Career\TimelineEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Contenu pouvant servir de source à un événement de la chronologie (D10).
 *
 * Une relation polymorphe n'a pas de clé étrangère : ce trait supprime les événements liés
 * quand la source est supprimée définitivement, pour ne jamais laisser d'événement orphelin.
 * Une suppression douce les conserve (la source peut être restaurée).
 */
trait AppearsInTimeline
{
    /**
     * Enregistre la suppression des événements liés à la suppression définitive de la source.
     */
    protected static function bootAppearsInTimeline(): void
    {
        static::deleted(function (Model $source): void {
            if (method_exists($source, 'isForceDeleting') && ! $source->isForceDeleting()) {
                return;
            }

            TimelineEvent::query()
                ->where('eventable_type', $source->getMorphClass())
                ->where('eventable_id', $source->getKey())
                ->delete();
        });
    }

    /**
     * @return MorphMany<TimelineEvent, $this>
     */
    public function timelineEvents(): MorphMany
    {
        return $this->morphMany(TimelineEvent::class, 'eventable');
    }
}
