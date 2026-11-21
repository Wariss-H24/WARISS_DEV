<?php

namespace App\Enums;

/**
 * État d'un CV (domaine D15). Valeurs reprises dans la contrainte CHECK resumes_status_check.
 */
enum ResumeStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
