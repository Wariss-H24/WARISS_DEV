<?php

/*
|--------------------------------------------------------------------------
| Réglages propres à WARISS.DEV
|--------------------------------------------------------------------------
*/

return [

    /*
    | Adresse du site public (liens des e-mails, sitemap, aperçus des brouillons).
    */
    'site_url' => rtrim((string) env('SITE_URL', 'http://localhost:5173'), '/'),

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

    /*
    | Téléversements de la bibliothèque de médias (D14).
    |
    | Liste blanche : extension => types MIME réels acceptés (lus dans le contenu du fichier,
    | pas dans le nom), nature du média et taille maximale en kilo-octets. Le SVG est exclu
    | (il peut contenir du script) : les icônes passent par icon_key.
    | Les images sont ré-encodées : orientation appliquée, données EXIF (GPS, appareil) retirées.
    */
    'uploads' => [
        'types' => [
            'jpg' => ['mimes' => ['image/jpeg'], 'kind' => 'image', 'max_kb' => 10_240],
            'jpeg' => ['mimes' => ['image/jpeg'], 'kind' => 'image', 'max_kb' => 10_240],
            'png' => ['mimes' => ['image/png'], 'kind' => 'image', 'max_kb' => 10_240],
            'webp' => ['mimes' => ['image/webp'], 'kind' => 'image', 'max_kb' => 10_240],
            'pdf' => ['mimes' => ['application/pdf'], 'kind' => 'document', 'max_kb' => 20_480],
            'mp4' => ['mimes' => ['video/mp4'], 'kind' => 'video', 'max_kb' => 102_400],
            'webm' => ['mimes' => ['video/webm'], 'kind' => 'video', 'max_kb' => 102_400],
        ],
        'image_quality' => 85,
        'max_image_pixels' => 40_000_000,
        'directory' => 'media',
    ],

];
