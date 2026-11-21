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
     * Domaine D7 — Études de cas : présentation approfondie d'une réalisation,
     * découpée en sections typées. Les résultats chiffrés ne sont publiés que s'ils sont vérifiables.
     */
    public function up(): void
    {
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('project_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->smallInteger('reading_time_minutes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE case_studies
                ADD CONSTRAINT case_studies_dates_check CHECK (ended_on >= started_on),
                ADD CONSTRAINT case_studies_reading_time_check CHECK (reading_time_minutes > 0),
                ADD CONSTRAINT case_studies_status_check CHECK (status IN ('draft', 'scheduled', 'published', 'archived')),
                ADD CONSTRAINT case_studies_published_at_check CHECK (status NOT IN ('scheduled', 'published') OR published_at IS NOT NULL)
            SQL);
        DB::statement("CREATE INDEX case_studies_published_at_index ON case_studies (published_at) WHERE status = 'published'");

        Schema::create('case_study_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('case_study_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->string('summary', 500);
            $table->timestampsTz();

            $table->unique(['case_study_id', 'locale'], 'case_study_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'case_study_tr_locale_slug_unique');
        });

        Schema::create('case_study_sections', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('case_study_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->integer('position')->default(0);
            $table->timestampsTz();

            $table->index(['case_study_id', 'position']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE case_study_sections ADD CONSTRAINT case_study_sections_type_check CHECK (type IN (
                'context', 'need', 'objectives', 'constraints', 'challenge',
                'solution', 'architecture', 'results', 'lessons', 'custom'
            ))
            SQL);

        Schema::create('case_study_section_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('case_study_section_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title')->nullable();
            $table->text('body');
            $table->timestampsTz();

            $table->unique(['case_study_section_id', 'locale'], 'case_study_section_tr_owner_locale_unique');
        });

        Schema::create('case_study_technology', function (Blueprint $table) {
            $table->foreignId('case_study_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->index()->constrained()->cascadeOnDelete();

            $table->primary(['case_study_id', 'technology_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_study_technology');
        Schema::dropIfExists('case_study_section_translations');
        Schema::dropIfExists('case_study_sections');
        Schema::dropIfExists('case_study_translations');
        Schema::dropIfExists('case_studies');
    }
};
