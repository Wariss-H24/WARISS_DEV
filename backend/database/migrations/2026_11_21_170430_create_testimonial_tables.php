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
     * Domaine D13 — Témoignages. Un témoignage n'est publié qu'avec l'autorisation de son auteur :
     * la base refuse d'approuver un témoignage sans consentement.
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('author_name');
            $table->string('author_company')->nullable();
            $table->foreignId('photo_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->date('given_on')->nullable();
            $table->boolean('consent_given')->default(false);
            $table->string('status', 20)->default('pending');
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE testimonials
                ADD CONSTRAINT testimonials_status_check CHECK (status IN ('pending', 'approved', 'rejected')),
                ADD CONSTRAINT testimonials_approved_requires_consent_check CHECK (status <> 'approved' OR consent_given)
            SQL);

        Schema::create('testimonial_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('testimonial_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('quote');
            $table->string('author_role')->nullable();
            $table->timestampsTz();

            $table->unique(['testimonial_id', 'locale'], 'testimonial_tr_owner_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonial_translations');
        Schema::dropIfExists('testimonials');
    }
};
