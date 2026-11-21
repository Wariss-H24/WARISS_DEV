<?php

namespace App\Enums;

/**
 * Nature d'un message reçu (domaine D13). Une seule table pour le contact et les devis.
 * Valeurs reprises dans la contrainte CHECK messages_type_check.
 */
enum MessageType: string
{
    case Contact = 'contact';
    case Quote = 'quote';
    case Collaboration = 'collaboration';
}
