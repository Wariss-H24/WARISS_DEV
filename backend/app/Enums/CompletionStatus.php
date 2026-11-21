<?php

namespace App\Enums;

/**
 * État d'une formation (domaine D8) : évite d'afficher comme obtenu un diplôme qui ne l'est pas.
 * Valeurs reprises dans la contrainte CHECK educations_completion_status_check.
 */
enum CompletionStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Interrupted = 'interrupted';
}
