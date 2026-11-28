<?php

namespace App\Http\Requests\Admin\Identity;

use App\Enums\AvailabilityStatus;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Identity\Profile;
use Illuminate\Validation\Rule;

/**
 * Profil public du propriétaire (D1), ressource unique : le premier enregistrement le crée
 * (tous les champs obligatoires exigés), les suivants le modifient partiellement.
 */
class ProfileRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'first_name' => [...$this->requiredOnCreate(), 'string', 'max:100'],
            'last_name' => [...$this->requiredOnCreate(), 'string', 'max:100'],
            'display_name' => [...$this->requiredOnCreate(), 'string', 'max:150'],
            'public_email' => [...$this->requiredOnCreate(), 'string', 'email', 'max:255'],
            'public_phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 ().-]{6,30}$/'],
            'country_code' => $this->countryCodeRule(),
            'avatar_media_id' => $this->mediaRule(),
            'cover_media_id' => $this->mediaRule(),
            'availability_status' => ['sometimes', Rule::enum(AvailabilityStatus::class)],
            'available_from' => ['nullable', 'date'],
            'spoken_languages' => ['sometimes', 'array', 'max:20'],
            'spoken_languages.*' => ['array:code,level'],
            'spoken_languages.*.code' => ['required', 'string', 'regex:/^[a-z]{2}$/', 'distinct'],
            'spoken_languages.*.level' => ['required', 'string', Rule::in(['native', 'fluent', 'professional', 'intermediate', 'basic'])],
            ...$this->translationRules([
                'headline' => ['string', 'max:255'],
                'short_bio' => ['string', 'max:500'],
                'biography' => ['string', 'max:20000'],
                'location_label' => ['string', 'max:150'],
                'availability_note' => ['string', 'max:255'],
            ], ['headline', 'short_bio', 'biography']),
        ];
    }

    /**
     * Le profil est créé au premier enregistrement, quelle que soit la méthode (PUT ou PATCH).
     */
    protected function isCreating(): bool
    {
        return ! Profile::query()->exists();
    }
}
