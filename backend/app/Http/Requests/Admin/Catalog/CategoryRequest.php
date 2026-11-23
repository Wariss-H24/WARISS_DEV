<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Http\Requests\Admin\AdminFormRequest;

/**
 * Formulaire commun aux catégories simples (nom et slug traduits, position, activation) :
 * technologies, compétences, projets, expérimentations. La table de traduction est déduite
 * du préfixe de la route (ex. admin.technology-categories.store → technology_category_translations).
 */
class CategoryRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $entity = str((string) $this->route()?->getName())->after('admin.')->before('.')->singular()->replace('-', '_')->toString();

        return [
            'position' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['sometimes', 'boolean'],
            ...$this->translationRules(
                ['name' => ['string', 'max:255'], 'slug' => $this->slugFormat()],
                ['name', 'slug'],
                fn (string $locale): array => ['slug' => [$this->uniqueTranslatedSlug("{$entity}_translations", "{$entity}_id", $locale)]],
            ),
        ];
    }
}
