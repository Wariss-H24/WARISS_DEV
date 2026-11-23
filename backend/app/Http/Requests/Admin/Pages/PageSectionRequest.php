<?php

namespace App\Http\Requests\Admin\Pages;

use App\Enums\PageSectionType;
use App\Http\Requests\Admin\AdminFormRequest;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\Rule;

/**
 * Section de page et ses contenus mis en avant (items), remplacés en entier.
 * Seuls les types qui nettoient leurs mises en avant à la suppression sont acceptés (D-18).
 */
class PageSectionRequest extends AdminFormRequest
{
    public const FEATURABLE_TYPES = ['project', 'service', 'post', 'skill', 'certification'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'page_id' => $this->isCreating() ? ['required', 'integer', Rule::exists('pages', 'id')] : ['prohibited'],
            'type' => [...$this->requiredOnCreate(), Rule::enum(PageSectionType::class)],
            'is_active' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'config' => ['sometimes', 'array'],
            'items' => ['sometimes', 'array', 'max:50'],
            'items.*' => ['array:itemable_type,itemable_id'],
            'items.*.itemable_type' => ['required', 'string', Rule::in(self::FEATURABLE_TYPES)],
            'items.*.itemable_id' => ['required', 'integer', $this->itemExists()],
            ...$this->translationRules([
                'title' => ['string', 'max:255'],
                'subtitle' => ['string', 'max:500'],
                'body' => ['string', 'max:50000'],
                'cta_label' => ['string', 'max:100'],
                'cta_url' => $this->safeLink(),
            ]),
        ];
    }

    /**
     * Vérifie que le contenu mis en avant existe et n'est pas en double.
     */
    private function itemExists(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $index = (int) explode('.', $attribute)[1];
            $type = $this->input("items.{$index}.itemable_type");
            $modelClass = is_string($type) && in_array($type, self::FEATURABLE_TYPES, true) ? Relation::getMorphedModel($type) : null;

            /** @var class-string<Model>|null $modelClass */
            if ($modelClass === null || ! $modelClass::query()->whereKey($value)->exists()) {
                $fail(__('Le contenu mis en avant est introuvable.'));

                return;
            }

            $duplicates = collect((array) $this->input('items'))
                ->filter(fn (mixed $item): bool => is_array($item) && ($item['itemable_type'] ?? null) === $type && (int) ($item['itemable_id'] ?? 0) === (int) $value);

            if ($duplicates->count() > 1) {
                $fail(__('Ce contenu est déjà mis en avant dans cette section.'));
            }
        };
    }
}
