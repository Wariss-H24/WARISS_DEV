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
     * Domaine D12 — Blog. Une seule catégorie par article, plusieurs tags, pas de commentaires publics.
     * Le contenu est du Markdown, stocké tel quel et nettoyé au rendu (protection XSS côté affichage).
     */
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('post_category_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('post_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['post_category_id', 'locale'], 'post_category_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'post_category_tr_locale_slug_unique');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('author_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('post_category_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('cover_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->string('type', 20)->default('article');
            $table->smallInteger('reading_time_minutes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE posts
                ADD CONSTRAINT posts_type_check CHECK (type IN ('article', 'tutorial', 'guide', 'feedback', 'note', 'tip', 'news')),
                ADD CONSTRAINT posts_reading_time_check CHECK (reading_time_minutes > 0),
                ADD CONSTRAINT posts_status_check CHECK (status IN ('draft', 'scheduled', 'published', 'archived')),
                ADD CONSTRAINT posts_published_at_check CHECK (status NOT IN ('scheduled', 'published') OR published_at IS NOT NULL)
            SQL);
        DB::statement("CREATE INDEX posts_published_at_index ON posts (published_at) WHERE status = 'published'");

        Schema::create('post_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->string('excerpt', 500);
            $table->text('content');
            $table->timestampsTz();

            $table->unique(['post_id', 'locale'], 'post_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'post_tr_locale_slug_unique');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE post_translations ADD COLUMN search_vector tsvector GENERATED ALWAYS AS (
                setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(title, ''))), 'A')
                || setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(excerpt, ''))), 'B')
                || setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(content, ''))), 'C')
            ) STORED
            SQL);
        DB::statement('CREATE INDEX post_translations_search_vector_index ON post_translations USING GIN (search_vector)');

        Schema::create('tags', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->timestampsTz();
        });

        Schema::create('tag_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->timestampsTz();

            $table->unique(['tag_id', 'locale'], 'tag_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'tag_tr_locale_slug_unique');
        });

        Schema::create('post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->index()->constrained()->cascadeOnDelete();

            $table->primary(['post_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('tag_translations');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('post_translations');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_category_translations');
        Schema::dropIfExists('post_categories');
    }
};
