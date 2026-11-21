<?php

namespace App\Enums;

/**
 * Rôle d'un média rattaché à un contenu par la table mediables (domaine D14).
 * Valeurs reprises dans la contrainte CHECK mediables_role_check.
 */
enum MediableRole: string
{
    case Gallery = 'gallery';
    case Screenshot = 'screenshot';
    case Attachment = 'attachment';
}
