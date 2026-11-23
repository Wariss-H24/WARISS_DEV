<?php

namespace App\Support\Concerns;

use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Cycle éditorial (colonnes status et published_at).
 *
 * Règle unique du projet : un contenu est public seulement si status = published
 * ET published_at <= maintenant. Le modèle doit caster « status » en PublicationStatus
 * et « published_at » en datetime.
 */
trait HasPublicationStatus
{
    /**
     * Un contenu publié sans date de publication est daté de l'instant présent
     * (sinon la contrainte <table>_published_at_check refuserait l'enregistrement).
     */
    protected static function bootHasPublicationStatus(): void
    {
        static::saving(function (self $content): void {
            if ($content->status === PublicationStatus::Published && $content->published_at === null) {
                $content->published_at = now();
            }
        });
    }

    /**
     * Passe les contenus programmés dont la date est atteinte au statut « publié ».
     * Appelée chaque minute par la tâche planifiée content:publish-scheduled.
     */
    public static function publishDueScheduled(): int
    {
        return static::query()
            ->where('status', PublicationStatus::Scheduled->value)
            ->where('published_at', '<=', now())
            ->update(['status' => PublicationStatus::Published->value]);
    }

    /**
     * Contenus visibles par le public à l'instant présent.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), PublicationStatus::Published->value)
            ->where($this->qualifyColumn('published_at'), '<=', now());
    }

    /**
     * Indique si ce contenu est visible par le public à l'instant présent.
     */
    public function isPubliclyVisible(): bool
    {
        return $this->status === PublicationStatus::Published
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo(now());
    }
}
