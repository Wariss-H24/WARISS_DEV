<?php

namespace App\Enums;

/**
 * Nature d'un média (domaine D14). Valeurs reprises dans la contrainte CHECK media_kind_check.
 */
enum MediaKind: string
{
    case Image = 'image';
    case Video = 'video';
    case Document = 'document';
    case Icon = 'icon';
    case Other = 'other';
}
