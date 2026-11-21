<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Colonne jsonb contenant toujours un objet JSON (clés nommées), lu comme tableau associatif.
 *
 * Un tableau PHP vide serait encodé en liste « [] » par json_encode et refusé par les
 * contraintes CHECK jsonb_typeof(...) = 'object' : il est enregistré comme objet vide « {} ».
 *
 * @implements CastsAttributes<array<string, mixed>, array<string, mixed>|null>
 */
class AsJsonObject implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (! is_string($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value === null || $value === []) {
            return '{}';
        }

        return (string) json_encode($value, JSON_THROW_ON_ERROR);
    }
}
