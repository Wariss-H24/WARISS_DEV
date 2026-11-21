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
     * Domaine D8 — Parcours : expériences professionnelles et formations.
     * Les informations doivent être exactes : une expérience terminée a une date de fin,
     * une formation indique si elle est en cours, terminée ou interrompue.
     */
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('organization_name');
            $table->string('organization_url', 2048)->nullable();
            $table->foreignId('organization_logo_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->string('employment_type', 20);
            $table->boolean('is_remote')->default(false);
            $table->char('country_code', 2)->nullable();
            $table->date('started_on');
            $table->date('ended_on')->nullable();
            $table->boolean('is_current')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->integer('position')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE experiences
                ADD CONSTRAINT experiences_employment_type_check CHECK (employment_type IN (
                    'full_time', 'part_time', 'contract', 'freelance', 'internship', 'apprenticeship', 'volunteer'
                )),
                ADD CONSTRAINT experiences_current_dates_check CHECK (
                    (is_current AND ended_on IS NULL)
                    OR (NOT is_current AND ended_on IS NOT NULL AND ended_on >= started_on)
                ),
                ADD CONSTRAINT experiences_country_code_check CHECK (country_code ~ '^[A-Z]{2}$'),
                ADD CONSTRAINT experiences_organization_url_check CHECK (organization_url ~* '^https?://')
            SQL);

        Schema::create('experience_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('job_title');
            $table->string('location_label', 150)->nullable();
            $table->string('summary', 500);
            $table->text('description');
            $table->text('achievements')->nullable();
            $table->timestampsTz();

            $table->unique(['experience_id', 'locale'], 'experience_tr_owner_locale_unique');
        });

        Schema::create('experience_technology', function (Blueprint $table) {
            $table->foreignId('experience_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->index()->constrained()->cascadeOnDelete();

            $table->primary(['experience_id', 'technology_id']);
        });

        Schema::create('educations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('institution_name');
            $table->string('institution_url', 2048)->nullable();
            $table->foreignId('institution_logo_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->string('degree_level', 20);
            $table->string('completion_status', 20);
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->char('country_code', 2)->nullable();
            $table->foreignId('proof_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->integer('position')->default(0);
            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE educations
                ADD CONSTRAINT educations_degree_level_check CHECK (degree_level IN (
                    'certificate', 'diploma', 'bachelor', 'master', 'doctorate', 'bootcamp', 'self_taught', 'other'
                )),
                ADD CONSTRAINT educations_completion_status_check CHECK (completion_status IN ('in_progress', 'completed', 'interrupted')),
                ADD CONSTRAINT educations_dates_check CHECK (ended_on >= started_on),
                ADD CONSTRAINT educations_country_code_check CHECK (country_code ~ '^[A-Z]{2}$'),
                ADD CONSTRAINT educations_institution_url_check CHECK (institution_url ~* '^https?://')
            SQL);

        Schema::create('education_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('education_id')->constrained('educations')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('degree_title');
            $table->string('field_of_study')->nullable();
            $table->string('location_label', 150)->nullable();
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['education_id', 'locale'], 'education_tr_owner_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_translations');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('experience_technology');
        Schema::dropIfExists('experience_translations');
        Schema::dropIfExists('experiences');
    }
};
