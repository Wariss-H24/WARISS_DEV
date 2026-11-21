<?php

namespace App\Support\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ordre d'affichage défini dans l'administration (colonne position), puis ordre de création.
 */
trait OrderedByPosition
{
    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy($this->qualifyColumn('position'))->orderBy($this->qualifyColumn('id'));
    }
}
