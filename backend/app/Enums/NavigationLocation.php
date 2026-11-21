<?php

namespace App\Enums;

/**
 * Emplacement d'un menu dans le site (domaine D2). Un emplacement accueille un seul menu.
 * Valeurs reprises dans la contrainte CHECK navigation_menus_location_check.
 */
enum NavigationLocation: string
{
    case Header = 'header';
    case Footer = 'footer';
    case Secondary = 'secondary';
    case Legal = 'legal';
}
