<?php

namespace App\Http\Controllers\Api\V1\Admin\Settings;

use App\Enums\ActivityEvent;
use App\Enums\SettingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\SiteSettingsRequest;
use App\Models\Settings\SiteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Réglages du site (D2) : lecture de tous les réglages (le compte viewer ne voit que les réglages
 * publics) et mise à jour par lot, en une seule transaction.
 */
class SiteSettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $settings = SiteSetting::query()
            ->with('translations')
            ->when(Gate::denies('view-sensitive-data'), fn ($query) => $query->where('is_public', true))
            ->when($request->filled('group'), fn ($query) => $query->where('group', $request->string('group')->toString()))
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        return response()->json(['data' => $settings->map($this->present(...))->values()]);
    }

    public function update(SiteSettingsRequest $request, ActivityLogger $activityLogger): JsonResponse
    {
        /** @var list<array{group: string, key: string, value?: mixed, translations?: array<string, string|null>}> $entries */
        $entries = $request->validated('settings');

        DB::transaction(function () use ($entries, $request, $activityLogger): void {
            foreach ($entries as $entry) {
                /** @var SiteSetting $setting */
                $setting = $request->targets()->get($request->identifier($entry));

                if ($setting->is_translatable) {
                    foreach ($entry['translations'] ?? [] as $locale => $value) {
                        $setting->translations()->updateOrCreate(['locale' => $locale], ['value' => $value]);
                    }
                    $setting->touch();
                } else {
                    $setting->value = $this->normalize($setting->type, $entry['value'] ?? null);
                    $setting->save();
                }

                $activityLogger->log(ActivityEvent::SettingsChanged, $setting, ['value']);
            }
        });

        return $this->index($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(SiteSetting $setting): array
    {
        return [
            'id' => $setting->id,
            'group' => $setting->group,
            'key' => $setting->key,
            'type' => $setting->type->value,
            'is_translatable' => $setting->is_translatable,
            'is_public' => $setting->is_public,
            'value' => $setting->is_translatable ? null : $setting->value,
            'translations' => $setting->is_translatable
                ? $setting->translations->mapWithKeys(fn ($translation): array => [$translation->locale => $translation->value])->all()
                : null,
            'updated_at' => $setting->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Valeur stockée avec le type JSON exact exigé par la contrainte site_settings_value_type_check.
     */
    private function normalize(SettingType $type, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            SettingType::Bool => (bool) $value,
            SettingType::Int, SettingType::Media => (int) $value,
            SettingType::String, SettingType::Text => (string) $value,
            SettingType::Json => $value,
        };
    }
}
