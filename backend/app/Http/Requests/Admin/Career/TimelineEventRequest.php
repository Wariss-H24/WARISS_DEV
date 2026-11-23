<?php

namespace App\Http\Requests\Admin\Career;

use App\Enums\TimelineEventType;
use App\Http\Requests\Admin\AdminFormRequest;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\Rule;

/**
 * Un événement peut reprendre un contenu du parcours (sa source) : le type de la source
 * doit alors être celui de l'événement (contrainte timeline_events_source_check).
 */
class TimelineEventRequest extends AdminFormRequest
{
    /**
     * Types d'événement qui peuvent reprendre un contenu existant.
     */
    public const SOURCE_TYPES = ['education', 'experience', 'certification', 'project', 'achievement'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => [...$this->requiredOnCreate(), Rule::enum(TimelineEventType::class)],
            'event_date' => [...$this->requiredOnCreate(), 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'eventable_type' => ['nullable', 'string', 'required_with:eventable_id', Rule::in(self::SOURCE_TYPES), 'same:type'],
            'eventable_id' => ['nullable', 'integer', 'required_with:eventable_type', $this->sourceExists()],
            'image_media_id' => $this->mediaRule(),
            ...$this->displayRules(featurable: false),
            ...$this->translationRules(['title' => ['string', 'max:255'], 'description' => ['string', 'max:5000']]),
        ];
    }

    /**
     * Vérifie que le contenu source existe (type résolu par la morph map).
     */
    private function sourceExists(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $type = $this->input('eventable_type');
            $modelClass = is_string($type) && in_array($type, self::SOURCE_TYPES, true) ? Relation::getMorphedModel($type) : null;

            /** @var class-string<Model>|null $modelClass */
            if ($modelClass === null || ! $modelClass::query()->whereKey($value)->exists()) {
                $fail(__('Le contenu source de cet événement est introuvable.'));
            }
        };
    }
}
