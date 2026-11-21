<?php

namespace App\Enums;

/**
 * Nature d'une caractéristique de service (domaine D3).
 * Valeurs reprises dans la contrainte CHECK service_features_kind_check.
 */
enum ServiceFeatureKind: string
{
    /** Bénéfice pour le client. */
    case Benefit = 'benefit';

    /** Élément inclus dans la prestation. */
    case Included = 'included';

    /** Livrable remis au client. */
    case Deliverable = 'deliverable';
}
