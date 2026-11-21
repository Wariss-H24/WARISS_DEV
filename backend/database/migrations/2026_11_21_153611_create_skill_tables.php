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
     * Domaine D5 — Compétences : ce que le propriétaire sait faire (une technologie est l'outil).
     * Le pivot project_skill est créé avec les projets (D6).
     */
    public function up(): void
    {
        Schema::create('skill_categories', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('skill_category_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('skill_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->timestampsTz();

            $table->unique(['skill_category_id', 'locale'], 'skill_category_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'skill_category_tr_locale_slug_unique');
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('skill_category_id')->index()->constrained()->restrictOnDelete();
            $table->string('slug')->unique();
            $table->smallInteger('proficiency')->nullable();
            $table->decimal('years_of_experience', 3, 1)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE skills
                ADD CONSTRAINT skills_proficiency_check CHECK (proficiency BETWEEN 1 AND 5),
                ADD CONSTRAINT skills_years_of_experience_check CHECK (years_of_experience >= 0)
            SQL);

        Schema::create('skill_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['skill_id', 'locale'], 'skill_tr_owner_locale_unique');
        });

        Schema::create('skill_technology', function (Blueprint $table) {
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->index()->constrained()->cascadeOnDelete();

            $table->primary(['skill_id', 'technology_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_technology');
        Schema::dropIfExists('skill_translations');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_category_translations');
        Schema::dropIfExists('skill_categories');
    }
};
