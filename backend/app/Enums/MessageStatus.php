<?php

namespace App\Enums;

/**
 * Traitement d'un message dans l'administration (domaine D13).
 * Valeurs reprises dans la contrainte CHECK messages_status_check.
 */
enum MessageStatus: string
{
    case New = 'new';
    case Read = 'read';
    case InProgress = 'in_progress';
    case Replied = 'replied';
    case Archived = 'archived';
    case Spam = 'spam';
}
