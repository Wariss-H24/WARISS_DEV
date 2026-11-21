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
     * Domaine D13 — Newsletter : abonnés (double confirmation, consentement daté, suppression
     * définitive sur demande), campagnes rédigées dans une seule langue, suivi des envois.
     * Le jeton de confirmation n'est jamais stocké en clair : seule son empreinte SHA-256 l'est.
     */
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('email');
            $table->char('locale', 2);
            $table->string('status', 20)->default('pending');
            $table->char('confirmation_token_hash', 64)->nullable()->unique();
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampTz('consented_at');
            $table->string('consent_source', 50);
            $table->timestampTz('unsubscribed_at')->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->timestampsTz();

            $table->index('status');
        });

        DB::statement('CREATE UNIQUE INDEX newsletter_subscribers_email_lower_unique ON newsletter_subscribers (lower(email))');
        DB::statement(<<<'SQL'
            ALTER TABLE newsletter_subscribers
                ADD CONSTRAINT newsletter_subscribers_status_check CHECK (status IN ('pending', 'subscribed', 'unsubscribed', 'bounced')),
                ADD CONSTRAINT newsletter_subscribers_email_check CHECK (email ~ '^[^@\s]+@[^@\s]+\.[^@\s]+$'),
                ADD CONSTRAINT newsletter_subscribers_locale_check CHECK (locale ~ '^[a-z]{2}$'),
                ADD CONSTRAINT newsletter_subscribers_subscribed_check CHECK (status <> 'subscribed' OR confirmed_at IS NOT NULL),
                ADD CONSTRAINT newsletter_subscribers_unsubscribed_check CHECK (status <> 'unsubscribed' OR unsubscribed_at IS NOT NULL),
                ADD CONSTRAINT newsletter_subscribers_token_hash_check CHECK (confirmation_token_hash ~ '^[0-9a-f]{64}$'),
                ADD CONSTRAINT newsletter_subscribers_ip_hash_check CHECK (ip_hash ~ '^[0-9a-f]{64}$')
            SQL);

        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->char('locale', 2);
            $table->string('subject');
            $table->string('preview_text')->nullable();
            $table->text('body');
            $table->string('status', 20)->default('draft');
            $table->timestampTz('scheduled_at')->nullable();
            $table->timestampTz('sent_at')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE newsletter_campaigns
                ADD CONSTRAINT newsletter_campaigns_status_check CHECK (status IN ('draft', 'scheduled', 'sending', 'sent', 'failed')),
                ADD CONSTRAINT newsletter_campaigns_locale_check CHECK (locale ~ '^[a-z]{2}$'),
                ADD CONSTRAINT newsletter_campaigns_scheduled_check CHECK (status <> 'scheduled' OR scheduled_at IS NOT NULL),
                ADD CONSTRAINT newsletter_campaigns_sent_check CHECK (status <> 'sent' OR sent_at IS NOT NULL),
                ADD CONSTRAINT newsletter_campaigns_recipients_count_check CHECK (recipients_count >= 0)
            SQL);

        Schema::create('newsletter_campaign_recipients', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('campaign_id')->constrained('newsletter_campaigns')->cascadeOnDelete();
            $table->foreignId('subscriber_id')->index()->constrained('newsletter_subscribers')->cascadeOnDelete();
            $table->string('status', 20)->default('queued');
            $table->timestampTz('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestampsTz();

            $table->unique(['campaign_id', 'subscriber_id']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE newsletter_campaign_recipients
                ADD CONSTRAINT newsletter_campaign_recipients_status_check CHECK (status IN ('queued', 'sent', 'failed', 'bounced')),
                ADD CONSTRAINT newsletter_campaign_recipients_sent_check CHECK (status <> 'sent' OR sent_at IS NOT NULL)
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_campaign_recipients');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
