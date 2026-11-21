<?php

namespace App\Enums;

/**
 * Rôle d'un compte d'administration (décision D-04).
 *
 * Les valeurs sont reprises dans la contrainte CHECK de la table users.
 */
enum UserRole: string
{
    /** Tous les droits. */
    case Admin = 'admin';

    /** Lecture seule, sans accès aux données sensibles. */
    case Viewer = 'viewer';
}
