<?php

namespace App\Enums;

/**
 * Nature d'une réalisation ou distinction (domaine D9).
 * Valeurs reprises dans la contrainte CHECK achievements_type_check.
 */
enum AchievementType: string
{
    case Award = 'award';
    case Distinction = 'distinction';
    case Publication = 'publication';
    case Talk = 'talk';
    case Hackathon = 'hackathon';
    case OpenSource = 'open_source';
    case Milestone = 'milestone';
    case Other = 'other';
}
