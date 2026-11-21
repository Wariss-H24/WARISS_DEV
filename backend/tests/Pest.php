<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Les tests de fonctionnalité utilisent l'application Laravel complète et la
| base PostgreSQL de test (wariss_dev_test, voir phpunit.xml), remise à zéro
| par RefreshDatabase (chaque test s'exécute dans une transaction annulée).
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
