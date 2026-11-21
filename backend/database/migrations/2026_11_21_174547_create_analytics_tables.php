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
     * Domaine D17 — Mesure d'audience sans cookie ni donnée personnelle (D-16) :
     * aucune adresse IP, seulement une empreinte de session quotidienne non réversible.
     * Données brutes conservées 90 jours (purge planifiée), agrégats quotidiens conservés.
     * Les références viewable / eventable ne sont pas nettoyées à la suppression d'un contenu :
     * l'historique d'audience reste exact.
     */
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->timestampTz('viewed_at')->useCurrent();
            $table->string('path', 2048);
            $table->char('locale', 2);
            $table->string('viewable_type', 50)->nullable();
            $table->bigInteger('viewable_id')->nullable();
            $table->char('session_hash', 64);
            $table->string('referrer_host')->nullable();
            $table->string('referrer_type', 20)->default('direct');
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('device_type', 20);
            $table->char('country_code', 2)->nullable();

            $table->index('viewed_at');
            $table->index(['viewable_type', 'viewable_id']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE page_views
                ADD CONSTRAINT page_views_path_check CHECK (path ~ '^/'),
                ADD CONSTRAINT page_views_locale_check CHECK (locale ~ '^[a-z]{2}$'),
                ADD CONSTRAINT page_views_session_hash_check CHECK (session_hash ~ '^[0-9a-f]{64}$'),
                ADD CONSTRAINT page_views_referrer_type_check CHECK (referrer_type IN ('direct', 'search', 'social', 'other')),
                ADD CONSTRAINT page_views_device_type_check CHECK (device_type IN ('desktop', 'mobile', 'tablet')),
                ADD CONSTRAINT page_views_country_code_check CHECK (country_code ~ '^[A-Z]{2}$'),
                ADD CONSTRAINT page_views_viewable_check CHECK ((viewable_type IS NULL) = (viewable_id IS NULL))
            SQL);

        Schema::create('events', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->timestampTz('occurred_at')->useCurrent();
            $table->string('name', 30);
            $table->string('path', 2048);
            $table->string('eventable_type', 50)->nullable();
            $table->bigInteger('eventable_id')->nullable();
            $table->char('session_hash', 64);
            $table->jsonb('properties')->default(new Expression("'{}'::jsonb"));

            $table->index(['name', 'occurred_at']);
            $table->index('occurred_at');
            $table->index(['eventable_type', 'eventable_id']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE events
                ADD CONSTRAINT events_name_check CHECK (name IN (
                    'cv_download', 'cta_click', 'contact_submit', 'newsletter_subscribe',
                    'demo_open', 'outbound_click', 'share'
                )),
                ADD CONSTRAINT events_path_check CHECK (path ~ '^/'),
                ADD CONSTRAINT events_session_hash_check CHECK (session_hash ~ '^[0-9a-f]{64}$'),
                ADD CONSTRAINT events_properties_check CHECK (jsonb_typeof(properties) = 'object'),
                ADD CONSTRAINT events_eventable_check CHECK ((eventable_type IS NULL) = (eventable_id IS NULL)),
                ADD CONSTRAINT events_share_network_check CHECK (
                    name <> 'share'
                    OR coalesce(properties->>'network', '') IN ('linkedin', 'x', 'facebook', 'whatsapp', 'email', 'copy_link')
                )
            SQL);

        Schema::create('analytics_daily_stats', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->date('stat_date');
            $table->string('metric', 50);
            $table->string('dimension_type', 50)->default('');
            $table->string('dimension_value')->default('');
            $table->bigInteger('value');
            $table->timestampsTz();

            $table->unique(['stat_date', 'metric', 'dimension_type', 'dimension_value'], 'analytics_daily_stats_dimension_unique');
        });

        DB::statement('ALTER TABLE analytics_daily_stats ADD CONSTRAINT analytics_daily_stats_value_check CHECK (value >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_stats');
        Schema::dropIfExists('events');
        Schema::dropIfExists('page_views');
    }
};
