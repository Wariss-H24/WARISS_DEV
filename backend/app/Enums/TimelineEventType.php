<?php

namespace App\Enums;

/**
 * Type d'un événement de la chronologie (domaine D10).
 * Valeurs reprises dans la contrainte CHECK timeline_events_type_check. Les cinq premiers types
 * correspondent aux alias de la morph map de leur source : un événement lié pointe vers une source du même type.
 */
enum TimelineEventType: string
{
    case Education = 'education';
    case Experience = 'experience';
    case Certification = 'certification';
    case Project = 'project';
    case Achievement = 'achievement';

    /** Étape d'apprentissage saisie manuellement. */
    case Learning = 'learning';

    /** Événement saisi manuellement (conférence, rencontre…). */
    case Event = 'event';
}
