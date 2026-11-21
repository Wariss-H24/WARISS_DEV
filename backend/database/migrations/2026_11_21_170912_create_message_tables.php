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
     * Domaine D13 — Messages de contact et demandes de devis (une seule table).
     * Données personnelles limitées : adresse IP jamais en clair (empreinte SHA-256),
     * consentement daté, purge au bout de 24 mois (tâche planifiée).
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('type', 20)->default('contact');
            $table->foreignId('service_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->jsonb('details')->default(new Expression("'{}'::jsonb"));
            $table->char('locale', 2);
            $table->boolean('consent_given')->default(false);
            $table->timestampTz('consented_at')->nullable();
            $table->string('status', 20)->default('new');
            $table->timestampTz('read_at')->nullable();
            $table->timestampTz('replied_at')->nullable();
            $table->text('internal_note')->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE messages
                ADD CONSTRAINT messages_type_check CHECK (type IN ('contact', 'quote', 'collaboration')),
                ADD CONSTRAINT messages_status_check CHECK (status IN ('new', 'read', 'in_progress', 'replied', 'archived', 'spam')),
                ADD CONSTRAINT messages_email_check CHECK (email ~ '^[^@\s]+@[^@\s]+\.[^@\s]+$'),
                ADD CONSTRAINT messages_locale_check CHECK (locale ~ '^[a-z]{2}$'),
                ADD CONSTRAINT messages_consent_check CHECK (NOT consent_given OR consented_at IS NOT NULL),
                ADD CONSTRAINT messages_details_check CHECK (jsonb_typeof(details) = 'object'),
                ADD CONSTRAINT messages_ip_hash_check CHECK (ip_hash ~ '^[0-9a-f]{64}$'),
                ADD CONSTRAINT messages_replied_at_check CHECK (status <> 'replied' OR replied_at IS NOT NULL)
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
