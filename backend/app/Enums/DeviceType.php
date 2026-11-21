<?php

namespace App\Enums;

/**
 * Catégorie d'appareil d'une visite (domaine D17), sans identification de l'appareil.
 * Valeurs reprises dans la contrainte CHECK page_views_device_type_check.
 */
enum DeviceType: string
{
    case Desktop = 'desktop';
    case Mobile = 'mobile';
    case Tablet = 'tablet';
}
