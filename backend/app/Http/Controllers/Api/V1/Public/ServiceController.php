<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Models\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Services publiés (D3). Tarif et délai ne sont renvoyés que s'ils sont renseignés.
 * Filtres : category, technology, featured.
 */
class ServiceController extends PublishedContentController
{
    protected function model(): string
    {
        return Service::class;
    }

    protected function listRelations(): array
    {
        return [
            'category.translations',
            'technologies' => self::visible(),
        ];
    }

    protected function detailRelations(): array
    {
        return [
            'features' => self::visible(),
            'gallery.translations',
        ];
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        self::filterByCategory($query, $request);
        self::filterByTechnology($query, $request);
        self::filterBySearch($query, $request);
    }
}
