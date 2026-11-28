<?php

namespace App\Http\Resources\Admin;

use App\Models\Media\Media;
use App\Services\Media\MediaStorage;
use Illuminate\Http\Request;

/**
 * Média pour l'administration : champs, traductions, URL d'affichage (publique, externe ou
 * route authentifiée pour un fichier privé) et, dans le détail, les contenus qui l'utilisent.
 */
class MediaResource extends AdminResource
{
    /**
     * @var list<array{table: string, column: string, ids: list<int>, count: int, blocking: bool}>|null
     */
    private ?array $usages = null;

    /**
     * Ajoute la liste des contenus qui utilisent le média (affichage détaillé).
     *
     * @param  list<array{table: string, column: string, ids: list<int>, count: int, blocking: bool}>  $usages
     */
    public function withUsages(array $usages): static
    {
        $this->usages = $usages;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Media $media */
        $media = $this->resource;

        return [
            ...self::payload($media),
            'url' => app(MediaStorage::class)->url($media),
            ...($this->usages === null ? [] : ['usages' => $this->usages]),
        ];
    }
}
