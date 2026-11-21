<?php

namespace App\Enums;

/**
 * Public visé par un CV (domaine D15). Valeurs reprises dans la contrainte CHECK resumes_target_check.
 */
enum ResumeTarget: string
{
    case General = 'general';
    case Freelance = 'freelance';
    case Employment = 'employment';
    case Other = 'other';
}
