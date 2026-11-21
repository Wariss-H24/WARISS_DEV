<?php

namespace App\Models\Identity;

use App\Enums\AvailabilityStatus;
use App\Models\Media\Media;
use App\Models\User;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Database\Factories\Identity\ProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Profil public du propriétaire (domaine D1), distinct du compte de connexion.
 *
 * Seules les informations choisies pour être publiques y figurent : jamais de mot de passe,
 * d'e-mail de connexion ni de rôle. Le compte rattaché (user_id) n'est pas assignable en masse.
 *
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $display_name
 * @property string $public_email
 * @property string|null $public_phone
 * @property string|null $country_code
 * @property int|null $avatar_media_id
 * @property int|null $cover_media_id
 * @property AvailabilityStatus $availability_status
 * @property Carbon|null $available_from
 * @property array<array-key, mixed> $spoken_languages
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Media|null $avatar
 * @property-read Media|null $cover
 * @property-read Collection<int, SocialLink> $socialLinks
 * @property-read int|null $social_links_count
 * @property-read ProfileTranslation|null $translation
 * @property-read Collection<int, ProfileTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read User $user
 *
 * @method static \Database\Factories\Identity\ProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile translated()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereAvailabilityStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereAvailableFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereAvatarMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCoverMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile wherePublicEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile wherePublicPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereSpokenLanguages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profile withTranslation(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[Table('profiles')]
#[Fillable([
    'first_name', 'last_name', 'display_name', 'public_email', 'public_phone', 'country_code',
    'avatar_media_id', 'cover_media_id', 'availability_status', 'available_from', 'spoken_languages',
])]
class Profile extends Model implements TranslatableContract
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory, Translatable;

    /**
     * @var list<string>
     */
    public array $translatedAttributes = ['headline', 'short_bio', 'biography', 'location_label', 'availability_note'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'avatar_media_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /**
     * @return HasMany<SocialLink, $this>
     */
    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'availability_status' => AvailabilityStatus::class,
            'available_from' => 'date',
            'spoken_languages' => 'array',
        ];
    }
}
