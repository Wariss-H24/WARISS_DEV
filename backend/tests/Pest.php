<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Les tests de fonctionnalité utilisent l'application Laravel complète.
| La base de test est PostgreSQL (wariss_dev_test, voir phpunit.xml).
| RefreshDatabase sera activé ici avec les premières migrations métier.
|
*/

pest()->extend(TestCase::class)
    ->in('Feature');
