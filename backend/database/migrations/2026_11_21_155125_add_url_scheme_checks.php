<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Décision D-15 : une URL enregistrée ne peut utiliser qu'un schéma sûr.
     * Bloque notamment « javascript: » et « data: », qui exécuteraient du code chez le visiteur (XSS).
     * Les tables créées ensuite déclarent leur contrainte dans leur propre migration.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE media ADD CONSTRAINT media_external_url_check CHECK (external_url ~* '^https?://')");
        DB::statement("ALTER TABLE technologies ADD CONSTRAINT technologies_official_url_check CHECK (official_url ~* '^https?://')");
        DB::statement("ALTER TABLE page_section_translations ADD CONSTRAINT page_section_translations_cta_url_check CHECK (cta_url ~* '^(https?://|mailto:|#|/([^/]|$))')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE page_section_translations DROP CONSTRAINT IF EXISTS page_section_translations_cta_url_check');
        DB::statement('ALTER TABLE technologies DROP CONSTRAINT IF EXISTS technologies_official_url_check');
        DB::statement('ALTER TABLE media DROP CONSTRAINT IF EXISTS media_external_url_check');
    }
};
