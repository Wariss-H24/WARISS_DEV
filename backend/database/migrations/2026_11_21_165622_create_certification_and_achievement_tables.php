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
     * Domaine D9 — Certifications et réalisations, à distinguer des projets et des expériences.
     * Seules les informations vérifiables sont publiées (identifiant et lien de vérification).
     */
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('issuer_name');
            $table->string('issuer_url', 2048)->nullable();
            $table->string('credential_id')->nullable();
            $table->string('verification_url', 2048)->nullable();
            $table->date('issued_on');
            $table->date('expires_on')->nullable();
            $table->foreignId('certificate_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE certifications
                ADD CONSTRAINT certifications_dates_check CHECK (expires_on >= issued_on),
                ADD CONSTRAINT certifications_issuer_url_check CHECK (issuer_url ~* '^https?://'),
                ADD CONSTRAINT certifications_verification_url_check CHECK (verification_url ~* '^https?://')
            SQL);

        Schema::create('certification_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['certification_id', 'locale'], 'certification_tr_owner_locale_unique');
        });

        Schema::create('certification_technology', function (Blueprint $table) {
            $table->foreignId('certification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->index()->constrained()->cascadeOnDelete();

            $table->primary(['certification_id', 'technology_id']);
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('type', 20);
            $table->date('achieved_on');
            $table->string('organization_name')->nullable();
            $table->string('url', 2048)->nullable();
            $table->foreignId('media_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE achievements
                ADD CONSTRAINT achievements_type_check CHECK (type IN (
                    'award', 'distinction', 'publication', 'talk', 'hackathon', 'open_source', 'milestone', 'other'
                )),
                ADD CONSTRAINT achievements_url_check CHECK (url ~* '^https?://')
            SQL);

        Schema::create('achievement_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['achievement_id', 'locale'], 'achievement_tr_owner_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_translations');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('certification_technology');
        Schema::dropIfExists('certification_translations');
        Schema::dropIfExists('certifications');
    }
};
