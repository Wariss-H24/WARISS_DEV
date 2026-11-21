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
