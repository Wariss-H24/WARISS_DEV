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
     * Domaine D16 — Métadonnées SEO des contenus publics, redirections et journal d'activité.
     * Le sitemap et le robots.txt sont générés, pas stockés.
     */
    public function up(): void
    {
        Schema::create('seo_metadata', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('seoable_type', 50);
            $table->bigInteger('seoable_id');
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->string('canonical_url', 2048)->nullable();
            $table->foreignId('og_image_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->string('schema_type', 50)->nullable();
            $table->jsonb('structured_data')->nullable();
            $table->timestampsTz();

            $table->unique(['seoable_type', 'seoable_id']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE seo_metadata
                ADD CONSTRAINT seo_metadata_seoable_type_check CHECK (seoable_type IN ('page', 'service', 'project', 'case_study', 'experiment', 'post')),
                ADD CONSTRAINT seo_metadata_canonical_url_check CHECK (canonical_url ~* '^https?://'),
                ADD CONSTRAINT seo_metadata_structured_data_check CHECK (jsonb_typeof(structured_data) = 'object')
            SQL);

        Schema::create('seo_metadata_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('seo_metadata_id')->constrained('seo_metadata')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 200)->nullable();
            $table->string('og_title', 100)->nullable();
            $table->string('og_description', 200)->nullable();
            $table->timestampsTz();

            $table->unique(['seo_metadata_id', 'locale'], 'seo_metadata_tr_owner_locale_unique');
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('from_path', 2048)->unique();
            $table->string('to_path', 2048);
            $table->smallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->integer('hits')->default(0);
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE redirects
                ADD CONSTRAINT redirects_status_code_check CHECK (status_code IN (301, 302, 307, 308)),
                ADD CONSTRAINT redirects_from_path_check CHECK (from_path ~ '^/([^/]|$)'),
                ADD CONSTRAINT redirects_to_path_check CHECK (to_path ~* '^(https?://|/([^/]|$))'),
                ADD CONSTRAINT redirects_not_to_itself_check CHECK (from_path <> to_path),
                ADD CONSTRAINT redirects_hits_check CHECK (hits >= 0)
            SQL);

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('event', 30);
            $table->string('subject_type', 50)->nullable();
            $table->bigInteger('subject_id')->nullable();
            $table->string('description')->nullable();
            $table->jsonb('properties')->default(new Expression("'{}'::jsonb"));
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE activity_logs
                ADD CONSTRAINT activity_logs_event_check CHECK (event IN (
                    'login', 'logout', 'created', 'updated', 'published', 'unpublished',
                    'deleted', 'settings_changed', 'password_reset'
                )),
                ADD CONSTRAINT activity_logs_properties_check CHECK (jsonb_typeof(properties) = 'object'),
                ADD CONSTRAINT activity_logs_subject_check CHECK ((subject_type IS NULL) = (subject_id IS NULL))
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('seo_metadata_translations');
        Schema::dropIfExists('seo_metadata');
    }
};
