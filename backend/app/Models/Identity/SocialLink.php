<?php

namespace App\Models\Identity;

use App\Support\Concerns\OrderedByPosition;
use Database\Factories\Identity\SocialLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Lien vers un réseau social ou une plateforme professionnelle (GitHub, LinkedIn…).
 *
 * @property int $id
 * @property int $profile_id
 * @property string $platform
 * @property string $url
 * @property bool $is_visible
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Profile $profile
 *
 * @method static \Database\Factories\Identity\SocialLinkFactory factory($count = null, $state = [])
 * @method static Builder<static>|SocialLink newModelQuery()
 * @method static Builder<static>|SocialLink newQuery()
 * @method static Builder<static>|SocialLink ordered()
 * @method static Builder<static>|SocialLink query()
 * @method static Builder<static>|SocialLink visible()
 * @method static Builder<static>|SocialLink whereCreatedAt($value)
 * @method static Builder<static>|SocialLink whereId($value)
 * @method static Builder<static>|SocialLink whereIsVisible($value)
 * @method static Builder<static>|SocialLink wherePlatform($value)
 * @method static Builder<static>|SocialLink wherePosition($value)
 * @method static Builder<static>|SocialLink whereProfileId($value)
 * @method static Builder<static>|SocialLink whereUpdatedAt($value)
 * @method static Builder<static>|SocialLink whereUrl($value)
 *
 * @mixin \Eloquent
 */
#[Table('social_links')]
#[Fillable(['platform', 'url', 'is_visible', 'position'])]
class SocialLink extends Model
{
    /** @use HasFactory<SocialLinkFactory> */
    use HasFactory, OrderedByPosition;

    /**
     * @return BelongsTo<Profile, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_visible'), true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'position' => 'integer',
        ];
    }
}
