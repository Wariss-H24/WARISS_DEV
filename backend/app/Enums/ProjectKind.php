<?php

namespace App\Enums;

/**
 * Nature d'un projet (domaine D6). Valeurs reprises dans la contrainte CHECK projects_kind_check.
 */
enum ProjectKind: string
{
    case Professional = 'professional';
    case Freelance = 'freelance';
    case Personal = 'personal';
    case Academic = 'academic';
    case OpenSource = 'open_source';
}
