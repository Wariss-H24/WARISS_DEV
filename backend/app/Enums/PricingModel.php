<?php

namespace App\Enums;

/**
 * Mode de tarification d'un service (domaine D3).
 * Valeurs reprises dans la contrainte CHECK services_pricing_model_check.
 */
enum PricingModel: string
{
    case Fixed = 'fixed';
    case Hourly = 'hourly';
    case Monthly = 'monthly';

    /** Sur devis : aucun prix affiché. */
    case Quote = 'quote';
}
