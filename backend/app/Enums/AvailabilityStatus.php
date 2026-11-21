<?php

namespace App\Enums;

/**
 * Disponibilité professionnelle affichée sur le profil public (domaine D1).
 * Valeurs reprises dans la contrainte CHECK profiles_availability_status_check.
 */
enum AvailabilityStatus: string
{
    case Available = 'available';
    case Limited = 'limited';
    case Unavailable = 'unavailable';
}
