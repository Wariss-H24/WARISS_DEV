<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Décision D-08 : les colonnes search_vector (générées) doivent ignorer les accents.
     * La fonction unaccent() native n'est pas IMMUTABLE, ce qu'exige une colonne générée :
     * immutable_unaccent() l'enveloppe avec un dictionnaire explicite, ce qui la rend sûre.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION immutable_unaccent(text)
            RETURNS text
            LANGUAGE sql
            IMMUTABLE PARALLEL SAFE STRICT
            AS $$ SELECT public.unaccent('public.unaccent'::regdictionary, $1) $$
            SQL);
    }

    /**
     * Reverse the migrations.
     *
     * L'extension unaccent est conservée : d'autres objets de la base peuvent en dépendre.
     */
    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS immutable_unaccent(text)');
    }
};
