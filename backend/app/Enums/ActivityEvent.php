<?php

namespace App\Enums;

/**
 * Action enregistrée dans le journal d'activité de l'administration (domaine D16).
 * Valeurs reprises dans la contrainte CHECK activity_logs_event_check.
 */
enum ActivityEvent: string
{
    case Login = 'login';
    case Logout = 'logout';
    case Created = 'created';
    case Updated = 'updated';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Deleted = 'deleted';
    case SettingsChanged = 'settings_changed';
    case PasswordReset = 'password_reset';
}
