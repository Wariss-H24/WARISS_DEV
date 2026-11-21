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
     * Domaine D6 — Portfolio et projets, avec le pivot project_skill (D5).
     * Les traductions de projets portent la colonne de recherche plein texte (D-08).
     */
    public function up(): void
    {
        Schema::create('project_categories', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('project_category_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('project_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->timestampsTz();

            $table->unique(['project_category_id', 'locale'], 'project_category_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'project_category_tr_locale_slug_unique');
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('project_category_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('cover_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->string('kind', 20);
            $table->string('development_stage', 20);
            $table->string('client_name')->nullable();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->string('live_url', 2048)->nullable();
            $table->string('repository_url', 2048)->nullable();
            $table->string('demo_url', 2048)->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE projects
                ADD CONSTRAINT projects_kind_check CHECK (kind IN ('professional', 'freelance', 'personal', 'academic', 'open_source')),
                ADD CONSTRAINT projects_development_stage_check CHECK (development_stage IN ('planned', 'in_progress', 'completed', 'maintained', 'paused')),
                ADD CONSTRAINT projects_dates_check CHECK (ended_on >= started_on),
                ADD CONSTRAINT projects_live_url_check CHECK (live_url ~* '^https?://'),
                ADD CONSTRAINT projects_repository_url_check CHECK (repository_url ~* '^https?://'),
                ADD CONSTRAINT projects_demo_url_check CHECK (demo_url ~* '^https?://'),
                ADD CONSTRAINT projects_status_check CHECK (status IN ('draft', 'scheduled', 'published', 'archived')),
                ADD CONSTRAINT projects_published_at_check CHECK (status NOT IN ('scheduled', 'published') OR published_at IS NOT NULL)
            SQL);
        DB::statement("CREATE INDEX projects_published_at_index ON projects (published_at) WHERE status = 'published'");

        Schema::create('project_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->string('summary', 500);
            $table->text('description');
            $table->text('context')->nullable();
            $table->string('role');
            $table->text('responsibilities')->nullable();
            $table->timestampsTz();

            $table->unique(['project_id', 'locale'], 'project_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'project_tr_locale_slug_unique');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE project_translations ADD COLUMN search_vector tsvector GENERATED ALWAYS AS (
                setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(title, ''))), 'A')
                || setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(summary, ''))), 'B')
                || setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(description, ''))), 'C')
            ) STORED
            SQL);
        DB::statement('CREATE INDEX project_translations_search_vector_index ON project_translations USING GIN (search_vector)');

        Schema::create('project_features', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->integer('position')->default(0);
            $table->timestampsTz();

            $table->index(['project_id', 'position']);
        });

        Schema::create('project_feature_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('project_feature_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['project_feature_id', 'locale'], 'project_feature_tr_owner_locale_unique');
        });

        Schema::create('project_technology', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->index()->constrained()->cascadeOnDelete();

            $table->primary(['project_id', 'technology_id']);
        });

        Schema::create('project_skill', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->index()->constrained()->cascadeOnDelete();

            $table->primary(['project_id', 'skill_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_skill');
        Schema::dropIfExists('project_technology');
        Schema::dropIfExists('project_feature_translations');
        Schema::dropIfExists('project_features');
        Schema::dropIfExists('project_translations');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_category_translations');
        Schema::dropIfExists('project_categories');
    }
};
