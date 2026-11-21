<?php

namespace App\Enums;

/**
 * Destination d'un élément de menu (domaine D2).
 * Valeurs reprises dans les contraintes CHECK navigation_items_type_check et navigation_items_destination_check.
 */
enum NavigationItemType: string
{
    /** Une page de la table pages (système ou personnalisée) : décision D-15. */
    case Page = 'page';

    /** Une route du frontend sans page en base (route_key). */
    case Route = 'route';

    /** Une URL externe (https ou mailto). */
    case External = 'external';
}
