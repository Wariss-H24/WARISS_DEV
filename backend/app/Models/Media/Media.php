<?php

namespace App\Models\Media;

use App\Enums\MediaKind;
use App\Enums\MediaVisibility;
use App\Models\User;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Media\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Fichier ou média externe de la bibliothèque centrale (domaine D14).
 *
 * Un média est soit un fichier stocké (disk, path, checksum…), soit une ressource
 * externe (external_url), jamais les deux : contrainte media_source_check.
 * Les champs techniques du fichier sont fixés par l'action de téléversement, pas par un formulaire.
 *
 * @property int $id
 * @property int|null $media_collection_id
 * @property string|null $disk
 * @property string|null $path
 * @property string|null $original_name
 * @property string|null $stored_name
 * @property string|null $mime_type
 * @property string|null $extension
 * @property int|null $size_bytes
 * @property string|null $checksum_sha256
 * @property string|null $external_url
 * @property int|null $width
 * @property int|null $height
 * @property int|null $duration_seconds
 * @property MediaKind $kind
 * @property MediaVisibility $visibility
 * @property string|null $credit
 * @property int|null $uploaded_by
 * @property array<array-key, mixed> $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read MediaCollection|null $collection
 * @property-read MediaTranslation|null $translation
 * @property-read Collection<int, MediaTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read User|null $uploader
 * @property-read Collection<int, Mediable> $usages
 * @property-read int|null $usages_count
 *
 * @method static \Database\Factories\Media\MediaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media translated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereChecksumSha256($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereDurationSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereExternalUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereKind($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereMediaCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereSizeBytes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereStoredName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('media')]
#[Fillable(['media_collection_id', 'kind', 'visibility', 'external_url', 'credit'])]
class Media extends Model implements TranslatableContract
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['alt_text', 'caption', 'description'];

    /**
     * @return BelongsTo<MediaCollection, $this>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(MediaCollection::class, 'media_collection_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Rattachements du média aux contenus (galeries, captures, pièces jointes).
     *
     * @return HasMany<Mediable, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(Mediable::class);
    }

    /**
     * Indique si le média est hébergé ailleurs (aucun fichier stocké).
     */
    public function isExternal(): bool
    {
        return $this->external_url !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => MediaKind::class,
            'visibility' => MediaVisibility::class,
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration_seconds' => 'integer',
            'metadata' => 'array',
        ];
    }
}
