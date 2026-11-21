<?php

namespace App\Enums;

/**
 * Avancement d'un projet (domaine D6), distinct de son statut de publication.
 * Valeurs reprises dans la contrainte CHECK projects_development_stage_check.
 */
enum ProjectStage: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Maintained = 'maintained';
    case Paused = 'paused';
}
