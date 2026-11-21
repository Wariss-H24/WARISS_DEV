<?php

namespace App\Enums;

/**
 * Provenance d'une visite (domaine D17). Valeurs reprises dans la contrainte CHECK page_views_referrer_type_check.
 */
enum ReferrerType: string
{
    case Direct = 'direct';
    case Search = 'search';
    case Social = 'social';
    case Other = 'other';
}
