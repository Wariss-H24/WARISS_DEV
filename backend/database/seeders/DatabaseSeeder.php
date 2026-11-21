<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Ordre prévu (documentation/02-DATABASE, étape 9) : comptes, pages système,
     * menus, réglages, catégories de départ. Les seeders sont ajoutés avec leurs modules.
     */
    public function run(): void
    {
        $this->call([
            AccountSeeder::class,
            CatalogSeeder::class,
        ]);
    }
}
