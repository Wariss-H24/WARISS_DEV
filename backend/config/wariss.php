<?php

/*
|--------------------------------------------------------------------------
| Réglages propres à WARISS.DEV
|--------------------------------------------------------------------------
*/

return [

    /*
    | Comptes d'administration créés par le seeder (décision D-04).
    |
    | Aucune donnée personnelle ni aucun mot de passe dans le code : tout vient
    | du .env, qui n'est jamais versionné. Un compte sans e-mail n'est pas créé.
    | Mot de passe vide : généré et affiché une seule fois (local), refusé en production.
    | Pour changer un mot de passe ensuite : php artisan app:set-password {email}
    */
    'seed_accounts' => [
        'admin' => [
            'name' => env('SEED_ADMIN_NAME', 'Administrateur'),
            'email' => env('SEED_ADMIN_EMAIL'),
            'password' => env('SEED_ADMIN_PASSWORD'),
        ],
        'viewer' => [
            'name' => env('SEED_VIEWER_NAME', 'Lecteur'),
            'email' => env('SEED_VIEWER_EMAIL'),
            'password' => env('SEED_VIEWER_PASSWORD'),
        ],
    ],

    /*
    | Durées de conservation des données (décision validée « Conservation »).
    | Les données plus anciennes sont supprimées par la tâche planifiée model:prune.
    */
    'retention' => [
        'messages_months' => 24,
        'activity_logs_months' => 12,
        'analytics_raw_days' => 90,
    ],

];
