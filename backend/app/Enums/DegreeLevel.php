<?php

namespace App\Enums;

/**
 * Niveau d'une formation (domaine D8). Valeurs reprises dans la contrainte CHECK educations_degree_level_check.
 */
enum DegreeLevel: string
{
    case Certificate = 'certificate';
    case Diploma = 'diploma';
    case Bachelor = 'bachelor';
    case Master = 'master';
    case Doctorate = 'doctorate';
    case Bootcamp = 'bootcamp';
    case SelfTaught = 'self_taught';
    case Other = 'other';
}
