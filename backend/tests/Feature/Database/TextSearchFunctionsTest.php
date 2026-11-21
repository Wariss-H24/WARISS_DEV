<?php

use Illuminate\Support\Facades\DB;

it('removes French accents with immutable_unaccent', function () {
    $result = DB::scalar('select immutable_unaccent(?)', ['Éléphant à Noël, œuvre déjà créée']);

    expect($result)->toBe('Elephant a Noel, oeuvre deja creee');
});

it('declares immutable_unaccent as immutable so generated columns can use it', function () {
    $volatility = DB::scalar("select provolatile from pg_proc where proname = 'immutable_unaccent'");

    expect($volatility)->toBe('i');
});
