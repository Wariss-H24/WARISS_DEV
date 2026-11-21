<?php

namespace App\Enums;

/**
 * État d'un abonnement à la newsletter (domaine D13), avec double confirmation.
 * Valeurs reprises dans la contrainte CHECK newsletter_subscribers_status_check.
 */
enum SubscriberStatus: string
{
    /** Inscrit, en attente de confirmation par e-mail. */
    case Pending = 'pending';

    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';

    /** Adresse invalide (rebond) : plus aucun envoi. */
    case Bounced = 'bounced';
}
