<?php

namespace App\Http\Requests\Admin\Pages;

use App\Enums\PageKind;
use App\Enums\PublicationStatus;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Pages\Page;
use Illuminate\Validation\Rule;

/**
 * Pages (D-18). La clé identifie la page dans le code : elle est fixée à la création.
 * Une page personnalisée est servie sous /:lang/pages/:slug (D-19).
 */
class PageRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'key' => $this->isCreating()
                ? ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/', Rule::unique('pages', 'key')]
                : ['prohibited'],
            'template' => ['sometimes', 'string', 'max:50', 'regex:/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/'],
            'status' => ['sometimes', Rule::enum(PublicationStatus::class)],
            'published_at' => ['nullable', 'date', 'required_if:status,scheduled', Rule::when($this->input('status') === 'scheduled', ['after:now'])],
            ...$this->translationRules(
                ['title' => ['string', 'max:255'], 'slug' => $this->slugFormat(), 'summary' => ['string', 'max:1000']],
                $this->isCreating() ? ['title', 'slug'] : ['title'],
                fn (string $locale): array => ['slug' => [
                    $this->uniqueTranslatedSlug('page_translations', 'page_id', $locale),
                    Rule::prohibitedIf(fn (): bool => $this->isSystemPage()),
                ]],
            ),
        ];
    }

    /**
     * Une page système a une route fixe du frontend : elle n'a pas de slug.
     */
    private function isSystemPage(): bool
    {
        $id = $this->routeId();

        return $id !== null && Page::query()->whereKey($id)->where('kind', PageKind::System->value)->exists();
    }
}
