<?php

namespace App\Enums;

/**
 * Réseau utilisé pour partager un contenu (décision D-16).
 * Valeurs reprises dans la contrainte CHECK events_share_network_check.
 */
enum ShareNetwork: string
{
    case Linkedin = 'linkedin';
    case X = 'x';
    case Facebook = 'facebook';
    case Whatsapp = 'whatsapp';
    case Email = 'email';
    case CopyLink = 'copy_link';
}
