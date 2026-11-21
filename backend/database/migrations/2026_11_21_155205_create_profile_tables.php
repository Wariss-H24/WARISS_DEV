<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Domaine D1 — Profil public, séparé du compte de connexion (users) :
     * aucune donnée d'authentification n'est exposée par le profil.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('display_name', 150);
            $table->string('public_email');
            $table->string('public_phone', 30)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->foreignId('avatar_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->string('availability_status', 20)->default('available');
            $table->date('available_from')->nullable();
            $table->jsonb('spoken_languages')->default(new Expression("'[]'::jsonb"));
            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE profiles
                ADD CONSTRAINT profiles_availability_status_check CHECK (availability_status IN ('available', 'limited', 'unavailable')),
                ADD CONSTRAINT profiles_country_code_check CHECK (country_code ~ '^[A-Z]{2}$'),
                ADD CONSTRAINT profiles_spoken_languages_check CHECK (jsonb_typeof(spoken_languages) = 'array')
            SQL);

        Schema::create('profile_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('headline');
            $table->string('short_bio', 500);
            $table->text('biography');
            $table->string('location_label', 150)->nullable();
            $table->string('availability_note')->nullable();
            $table->timestampsTz();

            $table->unique(['profile_id', 'locale'], 'profile_tr_owner_locale_unique');
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 50);
            $table->string('url', 2048);
            $table->boolean('is_visible')->default(true);
            $table->integer('position')->default(0);
            $table->timestampsTz();

            $table->index(['profile_id', 'position']);
        });

        DB::statement("ALTER TABLE social_links ADD CONSTRAINT social_links_url_check CHECK (url ~* '^(https?://|mailto:)')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('profile_translations');
        Schema::dropIfExists('profiles');
    }
};
