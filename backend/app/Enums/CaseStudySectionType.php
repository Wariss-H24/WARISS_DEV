<?php

namespace App\Enums;

/**
 * Type d'une section d'étude de cas (domaine D7). Remplace les anciennes tables challenges et solutions.
 * Valeurs reprises dans la contrainte CHECK case_study_sections_type_check.
 */
enum CaseStudySectionType: string
{
    case Context = 'context';
    case Need = 'need';
    case Objectives = 'objectives';
    case Constraints = 'constraints';
    case Challenge = 'challenge';
    case Solution = 'solution';
    case Architecture = 'architecture';
    case Results = 'results';
    case Lessons = 'lessons';
    case Custom = 'custom';
}
