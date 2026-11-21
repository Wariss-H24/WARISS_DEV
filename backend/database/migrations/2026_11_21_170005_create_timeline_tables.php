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
     * Domaine D10 — Chronologie. Aucune copie des autres modules : un événement lié à une source
     * (eventable) en reprend les données, ses traductions ne servant que de surcharge.
     * La source a toujours le même type que l'événement (alias de la morph map identiques).
     */
    public function up(): void
    {
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('type', 20);
            $table->date('event_date');
            $table->date('end_date')->nullable();
            $table->string('eventable_type', 50)->nullable();
            $table->bigInteger('eventable_id')->nullable();
            $table->foreignId('image_media_id')->nullable()->index()->constrained('media')->nullOnDelete();
            $table->boolean('is_visible')->default(true);
            $table->integer('position')->default(0);
            $table->timestampsTz();

            $table->index(['eventable_type', 'eventable_id']);
            $table->index('event_date');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE timeline_events
                ADD CONSTRAINT timeline_events_type_check CHECK (type IN (
                    'education', 'experience', 'certification', 'project', 'achievement', 'learning', 'event'
                )),
                ADD CONSTRAINT timeline_events_dates_check CHECK (end_date >= event_date),
                ADD CONSTRAINT timeline_events_source_check CHECK (
                    (eventable_type IS NULL AND eventable_id IS NULL)
                    OR (
                        eventable_type IS NOT NULL AND eventable_id IS NOT NULL AND eventable_type = type
                        AND type IN ('education', 'experience', 'certification', 'project', 'achievement')
                    )
                )
            SQL);

        Schema::create('timeline_event_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('timeline_event_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['timeline_event_id', 'locale'], 'timeline_event_tr_owner_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_event_translations');
        Schema::dropIfExists('timeline_events');
    }
};
