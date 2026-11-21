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
     * Domaine D14 — Médias : bibliothèque centrale réutilisée par tous les contenus.
     * Voir documentation/02-DATABASE (D14) et la décision D-13.
     */
    public function up(): void
    {
        Schema::create('media_collections', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            $table->timestampsTz();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('media_collection_id')->nullable()->index()->constrained()->nullOnDelete();

            // Fichier stocké : tous ces champs sont renseignés ensemble (contrainte media_source_check).
            $table->string('disk', 20)->nullable();
            $table->string('path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('stored_name')->nullable();
            $table->string('mime_type', 127)->nullable();
            $table->string('extension', 16)->nullable();
            $table->bigInteger('size_bytes')->nullable();
            $table->char('checksum_sha256', 64)->nullable()->index();

            // Média externe (vidéo hébergée ailleurs) : aucun fichier.
            $table->string('external_url', 2048)->nullable();

            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('kind', 20);
            $table->string('visibility', 10)->default('private');
            $table->string('credit')->nullable();
            $table->foreignId('uploaded_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->jsonb('metadata')->default(new Expression("'{}'::jsonb"));
            $table->timestampsTz();

            $table->unique(['disk', 'path']);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE media
                ADD CONSTRAINT media_disk_check CHECK (disk IN ('local', 'public', 's3')),
                ADD CONSTRAINT media_kind_check CHECK (kind IN ('image', 'video', 'document', 'icon', 'other')),
                ADD CONSTRAINT media_visibility_check CHECK (visibility IN ('public', 'private')),
                ADD CONSTRAINT media_size_check CHECK (size_bytes >= 0),
                ADD CONSTRAINT media_dimensions_check CHECK (
                    (width IS NULL AND height IS NULL)
                    OR (width IS NOT NULL AND height IS NOT NULL AND width > 0 AND height > 0)
                ),
                ADD CONSTRAINT media_duration_check CHECK (duration_seconds >= 0),
                ADD CONSTRAINT media_source_check CHECK (
                    (
                        external_url IS NULL
                        AND disk IS NOT NULL AND path IS NOT NULL AND original_name IS NOT NULL
                        AND stored_name IS NOT NULL AND mime_type IS NOT NULL AND extension IS NOT NULL
                        AND size_bytes IS NOT NULL AND checksum_sha256 IS NOT NULL
                    )
                    OR (
                        external_url IS NOT NULL
                        AND disk IS NULL AND path IS NULL AND stored_name IS NULL
                        AND size_bytes IS NULL AND checksum_sha256 IS NULL
                    )
                )
            SQL);

        Schema::create('media_translations', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->text('description')->nullable();
            $table->timestampsTz();

            $table->unique(['media_id', 'locale']);
        });

        Schema::create('mediables', function (Blueprint $table) {
            $table->id()->generatedAs();
            $table->foreignId('media_id')->constrained()->restrictOnDelete();
            $table->string('mediable_type', 50);
            $table->bigInteger('mediable_id');
            $table->string('role', 20);
            $table->integer('position')->default(0);
            $table->timestampsTz();

            $table->unique(['media_id', 'mediable_type', 'mediable_id', 'role']);
            $table->index(['mediable_type', 'mediable_id', 'role', 'position']);
        });

        DB::statement("ALTER TABLE mediables ADD CONSTRAINT mediables_role_check CHECK (role IN ('gallery', 'screenshot', 'attachment'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mediables');
        Schema::dropIfExists('media_translations');
        Schema::dropIfExists('media');
        Schema::dropIfExists('media_collections');
    }
};
