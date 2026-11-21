<?php

namespace App\Enums;

/**
 * Interaction mesurée sur le site public (domaine D17).
 * Valeurs reprises dans la contrainte CHECK events_name_check.
 */
enum AnalyticsEventName: string
{
    case CvDownload = 'cv_download';
    case CtaClick = 'cta_click';
    case ContactSubmit = 'contact_submit';
    case NewsletterSubscribe = 'newsletter_subscribe';
    case DemoOpen = 'demo_open';
    case OutboundClick = 'outbound_click';

    /** Clic sur un bouton de partage ; le réseau est dans properties.network (D-16). */
    case Share = 'share';
}
