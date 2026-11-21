<?php

namespace App\Enums;

/**
 * Visibilité d'un média (domaine D14). Valeurs reprises dans la contrainte CHECK media_visibility_check.
 *
 * Un média privé (justificatif de diplôme, ancienne version de CV…) n'est servi que
 * par une URL signée temporaire.
 */
enum MediaVisibility: string
{
    case Public = 'public';
    case Private = 'private';
}
