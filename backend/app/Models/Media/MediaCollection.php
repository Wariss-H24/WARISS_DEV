<?php

namespace App\Models\Media;

use Database\Factories\Media\MediaCollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Regroupement logique de médias dans la bibliothèque (domaine D14).
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Media> $media
 * @property-read int|null $media_count
 *
 * @method static \Database\Factories\Media\MediaCollectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaCollection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaCollection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaCollection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaCollection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaCollection whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaCollection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaCollection whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaCollection wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaCollection whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MediaCollection whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('media_collections')]
#[Fillable(['name', 'slug', 'description', 'position'])]
class MediaCollection extends Model
{
    /** @use HasFactory<MediaCollectionFactory> */
    use HasFactory;

    /**
     * @return HasMany<Media, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}
