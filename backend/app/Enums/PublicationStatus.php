<?php

namespace App\Enums;

/**
 * Cycle de vie d'un contenu éditorial (colonnes ED : pages, services, projets, articles…).
 * Valeurs reprises dans les contraintes CHECK <table>_status_check.
 *
 * Un contenu n'est public que s'il est « published » ET que sa date de publication est passée.
 * « scheduled » passe à « published » par la tâche planifiée de publication.
 */
enum PublicationStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';
}
