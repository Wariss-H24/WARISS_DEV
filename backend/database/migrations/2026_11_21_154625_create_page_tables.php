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
     * Domaine D18 — Pages et sections : structure des pages publiques administrable,
     * sans recopier les contenus des autres modules (page_section_items y fait référence).
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('key', 100)->unique();
            $table->string('kind', 20)->default('custom');
            $table->string('template', 50)->default('default');
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE pages
                ADD CONSTRAINT pages_kind_check CHECK (kind IN ('system', 'custom')),
                ADD CONSTRAINT pages_status_check CHECK (status IN ('draft', 'scheduled', 'published', 'archived')),
                ADD CONSTRAINT pages_published_at_check CHECK (status NOT IN ('scheduled', 'published') OR published_at IS NOT NULL)
            SQL);
        DB::statement("CREATE INDEX pages_published_at_index ON pages (published_at) WHERE status = 'published'");

        Schema::create('page_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('summary')->nullable();
            $table->timestampsTz();

            $table->unique(['page_id', 'locale'], 'page_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'page_tr_locale_slug_unique');
        });

        Schema::create('page_sections', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->jsonb('config')->default(new Expression("'{}'::jsonb"));
            $table->timestampsTz();

            $table->index(['page_id', 'position']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE page_sections ADD CONSTRAINT page_sections_type_check CHECK (type IN (
                'hero', 'about', 'skills', 'featured_projects', 'experiences', 'services',
                'latest_posts', 'cta', 'contact', 'richtext', 'gallery', 'faq'
            ))
            SQL);

        Schema::create('page_section_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('page_section_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->string('cta_label', 100)->nullable();
            $table->string('cta_url', 2048)->nullable();
            $table->timestampsTz();

            $table->unique(['page_section_id', 'locale'], 'page_section_tr_owner_locale_unique');
        });

        Schema::create('page_section_items', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('page_section_id')->constrained()->cascadeOnDelete();
            $table->string('itemable_type', 50);
            $table->bigInteger('itemable_id');
            $table->integer('position')->default(0);
            $table->timestampsTz();

            $table->unique(['page_section_id', 'itemable_type', 'itemable_id'], 'page_section_items_section_item_unique');
            $table->index(['itemable_type', 'itemable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_section_items');
        Schema::dropIfExists('page_section_translations');
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
    }
};
