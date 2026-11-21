<?php

namespace App\Enums;

/**
 * Type de la valeur d'un réglage du site (domaine D2).
 * Valeurs reprises dans les contraintes CHECK site_settings_type_check et site_settings_value_type_check.
 */
enum SettingType: string
{
    /** Texte court, non traduit (ex. nom du site). */
    case String = 'string';

    /** Texte long ; souvent traduit (ex. slogan, description). */
    case Text = 'text';

    case Bool = 'bool';
    case Int = 'int';

    /** Objet ou liste JSON. */
    case Json = 'json';

    /** Identifiant d'un média de la bibliothèque (ex. logo, favicon). */
    case Media = 'media';
}
