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
     * Domaine D3 — Services professionnels. Tarif et délai ne sont affichés que s'ils sont
     * renseignés ; la base garantit leur cohérence (prix positif avec devise, délai min ≤ max).
     */
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('icon', 100)->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('service_category_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['service_category_id', 'locale'], 'service_category_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'service_category_tr_locale_slug_unique');
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('service_category_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('cover_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->string('icon', 100)->nullable();
            $table->string('pricing_model', 20)->nullable();
            $table->decimal('price_from', 12, 2)->nullable();
            $table->char('price_currency', 3)->nullable();
            $table->integer('delivery_min_days')->nullable();
            $table->integer('delivery_max_days')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE services
                ADD CONSTRAINT services_pricing_model_check CHECK (pricing_model IN ('fixed', 'hourly', 'monthly', 'quote')),
                ADD CONSTRAINT services_price_check CHECK (price_from >= 0),
                ADD CONSTRAINT services_price_currency_check CHECK (price_currency ~ '^[A-Z]{3}$'),
                ADD CONSTRAINT services_price_requires_currency_check CHECK (price_from IS NULL OR price_currency IS NOT NULL),
                ADD CONSTRAINT services_quote_without_price_check CHECK (pricing_model IS DISTINCT FROM 'quote' OR price_from IS NULL),
                ADD CONSTRAINT services_delivery_min_check CHECK (delivery_min_days >= 0),
                ADD CONSTRAINT services_delivery_max_check CHECK (delivery_max_days >= 0),
                ADD CONSTRAINT services_delivery_range_check CHECK (delivery_max_days >= delivery_min_days),
                ADD CONSTRAINT services_status_check CHECK (status IN ('draft', 'scheduled', 'published', 'archived')),
                ADD CONSTRAINT services_published_at_check CHECK (status NOT IN ('scheduled', 'published') OR published_at IS NOT NULL)
            SQL);
        DB::statement("CREATE INDEX services_published_at_index ON services (published_at) WHERE status = 'published'");

        Schema::create('service_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->string('summary', 500);
            $table->text('description');
            $table->string('target_audience')->nullable();
            $table->string('cta_label', 100)->nullable();
            $table->timestampsTz();

            $table->unique(['service_id', 'locale'], 'service_tr_owner_locale_unique');
            $table->unique(['locale', 'slug'], 'service_tr_locale_slug_unique');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE service_translations ADD COLUMN search_vector tsvector GENERATED ALWAYS AS (
                setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(title, ''))), 'A')
                || setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(summary, ''))), 'B')
                || setweight(to_tsvector(CASE locale WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(coalesce(description, ''))), 'C')
            ) STORED
            SQL);
        DB::statement('CREATE INDEX service_translations_search_vector_index ON service_translations USING GIN (search_vector)');

        Schema::create('service_features', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('icon', 100)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->integer('position')->default(0);
            $table->timestampsTz();

            $table->index(['service_id', 'position']);
        });

        DB::statement("ALTER TABLE service_features ADD CONSTRAINT service_features_kind_check CHECK (kind IN ('benefit', 'included', 'deliverable'))");

        Schema::create('service_feature_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('service_feature_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['service_feature_id', 'locale'], 'service_feature_tr_owner_locale_unique');
        });

        Schema::create('service_technology', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->index()->constrained()->cascadeOnDelete();

            $table->primary(['service_id', 'technology_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_technology');
        Schema::dropIfExists('service_feature_translations');
        Schema::dropIfExists('service_features');
        Schema::dropIfExists('service_translations');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_category_translations');
        Schema::dropIfExists('service_categories');
    }
};
