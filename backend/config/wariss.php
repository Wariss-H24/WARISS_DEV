<?php

/*
|--------------------------------------------------------------------------
| Réglages propres à WARISS.DEV
|--------------------------------------------------------------------------
*/

return [

    /*
    | Comptes d'administration créés par le seeder (décision D-04).
    | Les mots de passe ne sont jamais écrits dans le code : ils viennent du .env.
    | En local, un mot de passe absent est généré et affiché une seule fois.
    */
    'seed_accounts' => [
        'admin' => [
            'name' => env('SEED_ADMIN_NAME', 'Administrateur'),
            'email' => env('SEED_ADMIN_EMAIL', 'admin@wariss.test'),
            'password' => env('SEED_ADMIN_PASSWORD'),
        ],
        'viewer' => [
            'name' => env('SEED_VIEWER_NAME', 'Lecteur'),
            'email' => env('SEED_VIEWER_EMAIL', 'viewer@wariss.test'),
            'password' => env('SEED_VIEWER_PASSWORD'),
        ],
    ],

];
