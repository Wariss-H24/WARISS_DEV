<?php

namespace App\Enums;

/**
 * Niveau de pratique d'une technologie (domaine D4).
 * Valeurs reprises dans la contrainte CHECK technologies_proficiency_check.
 *
 * À renseigner honnêtement : une technologie simplement découverte reste « learning ».
 */
enum TechnologyProficiency: string
{
    case Learning = 'learning';
    case Working = 'working';
    case Proficient = 'proficient';
    case Expert = 'expert';
}
