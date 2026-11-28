<?php

namespace App\Http\Requests\Admin\Settings;

use App\Enums\SettingType;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Settings\SiteSetting;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Mise à jour par lot des réglages du site (D2).
 *
 * Les réglages existent déjà (créés par SettingsSeeder) : on ne modifie que leur valeur, jamais leur
 * type ni leur clé. Chaque valeur est validée selon le type du réglage ; un réglage traduisible
 * reçoit une valeur par langue (« translations »), les autres une valeur unique (« value »).
 */
class SiteSettingsRequest extends AdminFormRequest
{
    /**
     * Réglages visés par la requête, indexés par « groupe.clé ».
     *
     * @var Collection<string, SiteSetting>|null
     */
    private ?Collection $targets = null;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'settings' => ['required', 'array', 'min:1', 'max:100'],
            'settings.*' => ['array:group,key,value,translations'],
            'settings.*.group' => ['required', 'string', 'max:50'],
            'settings.*.key' => ['required', 'string', 'max:100'],
        ];

        foreach ((array) $this->input('settings', []) as $index => $entry) {
            $setting = $this->targets()->get($this->identifier($entry));

            if ($setting === null) {
                $rules["settings.{$index}.key"][] = fn (string $attribute, mixed $value, Closure $fail) => $fail(__('Ce réglage n\'existe pas.'));

                continue;
            }

            $rules += $setting->is_translatable
                ? $this->translatedValueRules($index, $setting->type)
                : $this->plainValueRules($index, $setting);
        }

        return $rules;
    }

    /**
     * @return Collection<string, SiteSetting>
     */
    public function targets(): Collection
    {
        return $this->targets ??= SiteSetting::query()
            ->get()
            ->keyBy(fn (SiteSetting $setting): string => "{$setting->group}.{$setting->key}");
    }

    public function identifier(mixed $entry): string
    {
        return is_array($entry) ? ((string) ($entry['group'] ?? '')).'.'.((string) ($entry['key'] ?? '')) : '';
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function translatedValueRules(int|string $index, SettingType $type): array
    {
        /** @var list<string> $locales */
        $locales = config('translatable.locales');

        return [
            "settings.{$index}.value" => ['prohibited'],
            "settings.{$index}.translations" => ['required', 'array:'.implode(',', $locales)],
            "settings.{$index}.translations.*" => ['nullable', 'string', 'max:'.($type === SettingType::Text ? 5000 : 255)],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function plainValueRules(int|string $index, SiteSetting $setting): array
    {
        $valueRules = match ($setting->type) {
            SettingType::String => ['nullable', 'string', 'max:255', ...($setting->key === 'public_email' ? ['email'] : [])],
            SettingType::Text => ['nullable', 'string', 'max:5000'],
            SettingType::Bool => ['required', 'boolean'],
            SettingType::Int => ['nullable', 'integer', 'min:-2147483648', 'max:2147483647'],
            SettingType::Json => ['nullable', 'array'],
            SettingType::Media => ['nullable', 'integer', Rule::exists('media', 'id')],
        };

        return [
            "settings.{$index}.translations" => ['prohibited'],
            "settings.{$index}.value" => ['present', ...$valueRules],
        ];
    }
}
