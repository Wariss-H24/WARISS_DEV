<?php

namespace App\Models\Media;

use App\Enums\MediableRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Rattachement d'un média à un contenu (galerie, capture d'écran, pièce jointe).
 *
 * mediable_type contient un alias court (project, post…) défini dans la morph map,
 * jamais le nom complet d'une classe.
 *
 * @property int $id
 * @property int $media_id
 * @property string $mediable_type
 * @property int $mediable_id
 * @property MediableRole $role
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Media $media
 * @property-read Model $mediable
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable whereMediableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable whereMediableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mediable whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table('mediables')]
#[Fillable(['media_id', 'mediable_type', 'mediable_id', 'role', 'position'])]
class Mediable extends Model
{
    /**
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => MediableRole::class,
            'position' => 'integer',
        ];
    }
}
