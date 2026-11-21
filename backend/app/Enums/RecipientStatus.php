<?php

namespace App\Enums;

/**
 * État de l'envoi d'une campagne à un abonné (domaine D13).
 * Valeurs reprises dans la contrainte CHECK newsletter_campaign_recipients_status_check.
 */
enum RecipientStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';
    case Bounced = 'bounced';
}
