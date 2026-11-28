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
    ->beforeEach(function () {
        // Le client de test envoie « Accept-Language: en-us » par défaut : on simule un visiteur
        // francophone (langue par défaut du site) ; les tests en anglais passent ?locale=en.
        $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9');
    })
    ->in('Feature');
