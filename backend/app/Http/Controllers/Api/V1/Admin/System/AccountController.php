<?php

namespace App\Http\Controllers\Api\V1\Admin\System;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Models\User;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Comptes de l'administration (D-04), en lecture seule et réservés au compte admin.
 *
 * Il n'existe que deux comptes (admin et viewer) : ils sont créés par le seeder à partir du .env
 * et leurs mots de passe se changent avec « php artisan app:set-password », jamais par l'API.
 */
class AccountController extends AdminCrudController
{
    protected function model(): string
    {
        return User::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('role'),
            AllowedFilter::exact('is_active'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'name', 'last_login_at'];
    }

    protected function defaultSort(): string
    {
        return 'id';
    }
}
