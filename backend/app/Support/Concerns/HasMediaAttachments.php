<?php

namespace App\Support\Concerns;

use App\Enums\MediableRole;
use App\Models\Media\Media;
use App\Models\Media\Mediable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Médias rattachés à un contenu par la table mediables (galerie, captures, pièces jointes).
 *
 * Les images uniques (couverture, logo…) sont des clés étrangères directes ; ce trait ne gère
 * que les rattachements multiples. Le modèle doit figurer dans la morph map.
 * À la suppression définitive du contenu, ses rattachements sont retirés : sinon, les médias
 * resteraient « utilisés » (RESTRICT) et ne pourraient plus jamais être supprimés.
 */
trait HasMediaAttachments
{
    /**
     * Retire les rattachements de médias à la suppression définitive du contenu.
     */
    protected static function bootHasMediaAttachments(): void
    {
        static::deleted(function (Model $content): void {
            if (method_exists($content, 'isForceDeleting') && ! $content->isForceDeleting()) {
                return;
            }

            Mediable::query()
                ->where('mediable_type', $content->getMorphClass())
                ->where('mediable_id', $content->getKey())
                ->delete();
        });
    }

    /**
     * Tous les médias rattachés, dans l'ordre choisi.
     *
     * @return MorphToMany<Media, $this>
     */
    public function attachedMedia(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable', 'mediables')
            ->withPivot(['role', 'position'])
            ->withTimestamps()
            ->orderByPivot('position')
            ->orderBy('media.id');
    }

    /**
     * Images de la galerie.
     *
     * @return MorphToMany<Media, $this>
     */
    public function gallery(): MorphToMany
    {
        return $this->attachedMedia()->wherePivot('role', MediableRole::Gallery->value);
    }

    /**
     * Rattache un média avec un rôle, à la position donnée.
     */
    public function attachMedia(Media $media, MediableRole $role, int $position = 0): void
    {
        $this->attachedMedia()->attach($media->id, ['role' => $role->value, 'position' => $position]);
    }
}
