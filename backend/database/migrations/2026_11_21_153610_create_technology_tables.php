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
     * Domaine D4 — Technologies : catalogue central, chaque technologie n'existe qu'une fois
     * et est réutilisée par les projets, compétences, services, expériences… via des pivots.
     */
    public function up(): void
    {
        Schema::create('technology_categories', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('technology_category_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('technology_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->timestampsTz();

            $table->unique(['technology_category_id', 'locale'], 'technology_category_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'technology_category_tr_locale_slug_unique');
        });

        Schema::create('technologies', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('technology_category_id')->index()->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('icon_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->string('icon_key', 100)->nullable();
            $table->char('brand_color', 7)->nullable();
            $table->string('official_url', 2048)->nullable();
            $table->string('proficiency', 20)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE technologies
                ADD CONSTRAINT technologies_proficiency_check CHECK (proficiency IN ('learning', 'working', 'proficient', 'expert')),
                ADD CONSTRAINT technologies_brand_color_check CHECK (brand_color ~ '^#[0-9A-Fa-f]{6}$')
            SQL);

        Schema::create('technology_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['technology_id', 'locale'], 'technology_tr_owner_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technology_translations');
        Schema::dropIfExists('technologies');
        Schema::dropIfExists('technology_category_translations');
        Schema::dropIfExists('technology_categories');
    }
};
