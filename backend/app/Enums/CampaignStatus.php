<?php

namespace App\Enums;

/**
 * État d'une campagne de newsletter (domaine D13).
 * Valeurs reprises dans la contrainte CHECK newsletter_campaigns_status_check.
 */
enum CampaignStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sending = 'sending';
    case Sent = 'sent';
    case Failed = 'failed';
}
