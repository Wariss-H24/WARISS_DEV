<?php

namespace App\Http\Requests\Admin\Blog;

use App\Http\Requests\Admin\AdminFormRequest;

class TagRequest extends AdminFormRequest
{
    /**
     * Nom et slug traduits, limités à 100 caractères (taille des colonnes).
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = $this->sluggedTranslationRules('tag', labelField: 'name');

        foreach (array_keys($rules) as $key) {
            if (str_ends_with($key, '.name') || str_ends_with($key, '.slug')) {
                $rules[$key][] = 'max:100';
            }
        }

        return $rules;
    }
}
