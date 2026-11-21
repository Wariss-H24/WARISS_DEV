<?php

namespace App\Enums;

/**
 * Validation d'un témoignage (domaine D13). Valeurs reprises dans la contrainte CHECK testimonials_status_check.
 */
enum TestimonialStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
