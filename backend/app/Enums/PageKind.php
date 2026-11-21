<?php

namespace App\Enums;

/**
 * Nature d'une page (domaine D18). Valeurs reprises dans la contrainte CHECK pages_kind_check.
 */
enum PageKind: string
{
    /** Route fixe du site (accueil, contact…) : sans slug, jamais supprimée. */
    case System = 'system';

    /** Page éditoriale libre, avec un slug par langue. */
    case Custom = 'custom';
}
