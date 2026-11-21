<?php

namespace App\Enums;

/**
 * Type d'expérience professionnelle (domaine D8).
 * Valeurs reprises dans la contrainte CHECK experiences_employment_type_check.
 */
enum EmploymentType: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Freelance = 'freelance';
    case Internship = 'internship';
    case Apprenticeship = 'apprenticeship';
    case Volunteer = 'volunteer';
}
