<?php

namespace App\Models\Settings;

use App\Enums\SettingType;
use Database\Factories\Settings\SiteSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Réglage global du site (domaine D2), identifié par son groupe et sa clé.
 *
 * Valeur non traduisible : colonne value (jsonb, typée selon type).
 * Valeur traduisible : une ligne par langue dans site_setting_translations.
 * Le paquet Translatable n'est volontairement pas utilisé ici : la traduction porte le même
 * nom (value) que la colonne, ce qui masquerait la valeur des réglages non traduisibles.
 * Seuls les réglages is_public sont exposés par l'API publique.
 *
 * @property int $id
 * @property string $group
 * @property string $key
 * @property SettingType $type
 * @property array<array-key, mixed>|null $value
 * @property bool $is_translatable
 * @property bool $is_public
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, SiteSettingTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static Builder<static>|SiteSetting exposedToPublic()
 * @method static \Database\Factories\Settings\SiteSettingFactory factory($count = null, $state = [])
 * @method static Builder<static>|SiteSetting newModelQuery()
 * @method static Builder<static>|SiteSetting newQuery()
 * @method static Builder<static>|SiteSetting query()
 * @method static Builder<static>|SiteSetting whereCreatedAt($value)
 * @method static Builder<static>|SiteSetting whereGroup($value)
 * @method static Builder<static>|SiteSetting whereId($value)
 * @method static Builder<static>|SiteSetting whereIsPublic($value)
 * @method static Builder<static>|SiteSetting whereIsTranslatable($value)
 * @method static Builder<static>|SiteSetting whereKey($value)
 * @method static Builder<static>|SiteSetting whereType($value)
 * @method static Builder<static>|SiteSetting whereUpdatedAt($value)
 * @method static Builder<static>|SiteSetting whereValue($value)
 *
 * @mixin \Eloquent
 */
#[Table('site_settings')]
#[Fillable(['group', 'key', 'type', 'value', 'is_translatable', 'is_public'])]
class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    /**
     * @return HasMany<SiteSettingTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(SiteSettingTranslation::class);
    }

    /**
     * Valeur effective du réglage : traduction de la langue demandée (repli sur la langue
     * par défaut) pour un réglage traduisible, sinon la valeur stockée.
     */
    public function resolvedValue(?string $locale = null): mixed
    {
        if (! $this->is_translatable) {
            return $this->value;
        }

        $locale ??= app()->getLocale();
        $fallbackLocale = (string) config('translatable.fallback_locale');

        return $this->translations->firstWhere('locale', $locale)->value
            ?? $this->translations->firstWhere('locale', $fallbackLocale)->value
            ?? null;
    }

    /**
     * Réglages pouvant être exposés par l'API publique.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function exposedToPublic(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_public'), true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SettingType::class,
            'value' => 'json',
            'is_translatable' => 'boolean',
            'is_public' => 'boolean',
        ];
    }
}
