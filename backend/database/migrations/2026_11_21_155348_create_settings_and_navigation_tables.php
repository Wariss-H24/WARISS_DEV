<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Domaine D2 — Réglages du site et navigation. Les menus pointent vers les pages (D18),
     * créées par une migration précédente.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('group', 50);
            $table->string('key', 100);
            $table->string('type', 20);
            $table->jsonb('value')->nullable();
            $table->boolean('is_translatable')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestampsTz();

            $table->unique(['group', 'key']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE site_settings
                ADD CONSTRAINT site_settings_type_check CHECK (type IN ('string', 'text', 'bool', 'int', 'json', 'media')),
                ADD CONSTRAINT site_settings_translatable_check CHECK (NOT is_translatable OR (type IN ('string', 'text') AND value IS NULL)),
                ADD CONSTRAINT site_settings_value_type_check CHECK (
                    value IS NULL
                    OR (type IN ('string', 'text') AND jsonb_typeof(value) = 'string')
                    OR (type = 'bool' AND jsonb_typeof(value) = 'boolean')
                    OR (type IN ('int', 'media') AND jsonb_typeof(value) = 'number')
                    OR (type = 'json' AND jsonb_typeof(value) IN ('object', 'array'))
                )
            SQL);

        Schema::create('site_setting_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('site_setting_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('value')->nullable();
            $table->timestampsTz();

            $table->unique(['site_setting_id', 'locale'], 'site_setting_tr_owner_locale_unique');
        });

        Schema::create('navigation_menus', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('location', 20)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        DB::statement("ALTER TABLE navigation_menus ADD CONSTRAINT navigation_menus_location_check CHECK (location IN ('header', 'footer', 'secondary', 'legal'))");

        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('navigation_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->index()->constrained('navigation_items')->cascadeOnDelete();
            $table->string('type', 20);
            $table->foreignId('page_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->string('route_key', 100)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('icon', 100)->nullable();
            $table->boolean('opens_in_new_tab')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->integer('position')->default(0);
            $table->timestampsTz();

            $table->index(['navigation_menu_id', 'parent_id', 'position']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE navigation_items
                ADD CONSTRAINT navigation_items_type_check CHECK (type IN ('page', 'route', 'external')),
                ADD CONSTRAINT navigation_items_destination_check CHECK (
                    (type = 'page' AND page_id IS NOT NULL AND route_key IS NULL AND url IS NULL)
                    OR (type = 'route' AND route_key IS NOT NULL AND page_id IS NULL AND url IS NULL)
                    OR (type = 'external' AND url IS NOT NULL AND page_id IS NULL AND route_key IS NULL)
                ),
                ADD CONSTRAINT navigation_items_url_check CHECK (url ~* '^(https?://|mailto:)'),
                ADD CONSTRAINT navigation_items_not_own_parent_check CHECK (parent_id IS NULL OR parent_id <> id)
            SQL);

        Schema::create('navigation_item_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('navigation_item_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('label', 100);
            $table->timestampsTz();

            $table->unique(['navigation_item_id', 'locale'], 'navigation_item_tr_owner_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigation_item_translations');
        Schema::dropIfExists('navigation_items');
        Schema::dropIfExists('navigation_menus');
        Schema::dropIfExists('site_setting_translations');
        Schema::dropIfExists('site_settings');
    }
};
