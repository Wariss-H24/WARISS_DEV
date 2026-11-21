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
     * Domaine D11 — Laboratoire technique. Aucun code arbitraire n'est stocké ni exécuté :
     * une démonstration intégrée est désignée par demo_component_key, une clé de composant
     * de la liste blanche du frontend, au format strict (minuscules, chiffres, tirets).
     */
    public function up(): void
    {
        Schema::create('experiment_categories', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('experiment_category_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('experiment_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->timestampsTz();

            $table->unique(['experiment_category_id', 'locale'], 'experiment_category_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'experiment_category_tr_locale_slug_unique');
        });

        Schema::create('experiments', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('experiment_category_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('cover_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->string('kind', 20);
            $table->string('demo_url', 2048)->nullable();
            $table->string('repository_url', 2048)->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->string('demo_component_key', 100)->nullable();
            $table->date('experimented_on')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE experiments
                ADD CONSTRAINT experiments_kind_check CHECK (kind IN ('demo', 'prototype', 'tool', 'api_test', 'visualization', 'ui_experiment', 'learning')),
                ADD CONSTRAINT experiments_demo_url_check CHECK (demo_url ~* '^https?://'),
                ADD CONSTRAINT experiments_repository_url_check CHECK (repository_url ~* '^https?://'),
                ADD CONSTRAINT experiments_video_url_check CHECK (video_url ~* '^https?://'),
                ADD CONSTRAINT experiments_demo_component_key_check CHECK (demo_component_key ~ '^[a-z0-9]+(-[a-z0-9]+)*$'),
                ADD CONSTRAINT experiments_status_check CHECK (status IN ('draft', 'scheduled', 'published', 'archived')),
                ADD CONSTRAINT experiments_published_at_check CHECK (status NOT IN ('scheduled', 'published') OR published_at IS NOT NULL)
            SQL);
        DB::statement("CREATE INDEX experiments_published_at_index ON experiments (published_at) WHERE status = 'published'");

        Schema::create('experiment_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('experiment_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->string('summary', 500);
            $table->text('description');
            $table->text('objective')->nullable();
            $table->text('technical_notes')->nullable();
            $table->timestampsTz();

            $table->unique(['experiment_id', 'locale'], 'experiment_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'experiment_tr_locale_slug_unique');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE experiment_translations ADD COLUMN search_vector tsvector GENERATED ALWAYS AS (
                setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(title, ''))), 'A')
                || setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(summary, ''))), 'B')
                || setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(description, ''))), 'C')
            ) STORED
            SQL);
        DB::statement('CREATE INDEX experiment_translations_search_vector_index ON experiment_translations USING GIN (search_vector)');

        Schema::create('experiment_technology', function (Blueprint $table) {
            $table->foreignId('experiment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->index()->constrained()->cascadeOnDelete();

            $table->primary(['experiment_id', 'technology_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiment_technology');
        Schema::dropIfExists('experiment_translations');
        Schema::dropIfExists('experiments');
        Schema::dropIfExists('experiment_category_translations');
        Schema::dropIfExists('experiment_categories');
    }
};
