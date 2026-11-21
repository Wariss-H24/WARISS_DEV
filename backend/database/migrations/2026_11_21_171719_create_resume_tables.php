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
     * Domaine D15 — CV et versions. Seule la version courante d'un CV public et actif est
     * téléchargeable. Décision D-17 : la version courante doit appartenir au même CV (clé
     * étrangère composée), pour qu'aucune erreur ne rende public le fichier d'un autre CV.
     */
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('title');
            $table->string('target', 20)->default('general');
            $table->char('locale', 2);
            $table->string('status', 20)->default('draft');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_public')->default(false);
            $table->bigInteger('current_version_id')->nullable();
            $table->timestampsTz();
        });

        Schema::create('resume_versions', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->index()->constrained()->restrictOnDelete();
            $table->integer('version_number');
            $table->text('change_note')->nullable();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestampsTz();

            $table->unique(['resume_id', 'version_number']);
            $table->unique(['resume_id', 'id']);
        });

        DB::statement('ALTER TABLE resume_versions ADD CONSTRAINT resume_versions_version_number_check CHECK (version_number > 0)');

        DB::statement(<<<'SQL'
            ALTER TABLE resumes
                ADD CONSTRAINT resumes_current_version_foreign
                    FOREIGN KEY (id, current_version_id) REFERENCES resume_versions (resume_id, id)
                    ON DELETE SET NULL (current_version_id),
                ADD CONSTRAINT resumes_target_check CHECK (target IN ('general', 'freelance', 'employment', 'other')),
                ADD CONSTRAINT resumes_status_check CHECK (status IN ('draft', 'active', 'archived')),
                ADD CONSTRAINT resumes_locale_check CHECK (locale ~ '^[a-z]{2}$'),
                ADD CONSTRAINT resumes_public_requires_version_check CHECK (
                    NOT is_public OR (status = 'active' AND current_version_id IS NOT NULL)
                ),
                ADD CONSTRAINT resumes_default_requires_public_check CHECK (NOT is_default OR is_public)
            SQL);
        DB::statement('CREATE INDEX resumes_current_version_id_index ON resumes (current_version_id)');
        DB::statement('CREATE UNIQUE INDEX resumes_one_public_default_per_locale ON resumes (locale) WHERE is_default AND is_public');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE resumes DROP CONSTRAINT IF EXISTS resumes_current_version_foreign');
        Schema::dropIfExists('resume_versions');
        Schema::dropIfExists('resumes');
    }
};
