<?php

namespace App\Http\Requests\Admin;

use App\Enums\PublicationStatus;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

/**
 * Base des formulaires de l'administration.
 *
 * Création (POST) : les champs obligatoires sont exigés. Modification (PUT/PATCH) : chaque champ
 * envoyé est validé, les champs absents restent inchangés.
 * Traductions : bloc « translations.<langue> » ; le français (langue par défaut) est obligatoire
 * à la création, l'anglais est facultatif mais, s'il est envoyé, ses champs obligatoires le sont aussi.
 * Le contrôle des droits est fait par les middlewares de l'API d'administration.
 */
abstract class AdminFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Indique s'il s'agit d'une création (et non d'une modification).
     */
    protected function isCreating(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * Règle « obligatoire » adaptée : exigé à la création, facultatif mais non vide à la modification.
     *
     * @return list<string>
     */
    protected function requiredOnCreate(): array
    {
        return $this->isCreating() ? ['required'] : ['sometimes', 'required'];
    }

    /**
     * Identifiant du contenu modifié (paramètre de route {id}), ou null à la création.
     */
    protected function routeId(): ?int
    {
        $id = $this->route('id');

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * Valeur finale d'un champ après l'enregistrement : celle envoyée, sinon celle déjà stockée
     * (modification partielle). Sert aux règles qui croisent plusieurs champs.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function finalValue(string $modelClass, string $field): mixed
    {
        if ($this->has($field)) {
            return $this->input($field);
        }

        $id = $this->routeId();

        if ($id === null) {
            return null;
        }

        $value = $modelClass::query()->whereKey($id)->value($field);

        return $value instanceof BackedEnum ? $value->value : $value;
    }

    /**
     * Règles des champs traduits pour chaque langue.
     *
     * @param  array<string, array<int, mixed>>  $fields  règles par champ (sans required)
     * @param  list<string>  $requiredFields  champs obligatoires dans chaque langue fournie
     * @param  (callable(string): array<string, array<int, mixed>>)|null  $perLocaleRules  règles supplémentaires dépendant de la langue (ex. unicité du slug)
     * @return array<string, array<int, mixed>>
     */
    protected function translationRules(array $fields, array $requiredFields = [], ?callable $perLocaleRules = null): array
    {
        /** @var list<string> $locales */
        $locales = config('translatable.locales');
        $defaultLocale = (string) config('translatable.fallback_locale');

        $rules = ['translations' => [...($this->isCreating() ? ['required'] : ['sometimes']), 'array:'.implode(',', $locales)]];

        foreach ($locales as $locale) {
            $isDefault = $locale === $defaultLocale;
            $rules["translations.{$locale}"] = $isDefault && $this->isCreating() ? ['required', 'array'] : ['sometimes', 'array'];

            $localized = $perLocaleRules === null ? [] : $perLocaleRules($locale);

            foreach ($fields as $field => $fieldRules) {
                $presence = match (true) {
                    ! in_array($field, $requiredFields, true) => ['nullable'],
                    $isDefault && $this->isCreating() => ['required'],
                    default => ['sometimes', 'required'],
                };

                $rules["translations.{$locale}.{$field}"] = [...$presence, ...$fieldRules, ...($localized[$field] ?? [])];
            }
        }

        return $rules;
    }

    /**
     * Règles d'une liste d'éléments enfants traduits (fonctionnalités, sections…), remplacée en entier.
     *
     * @param  array<string, array<int, mixed>>  $fields  champs propres à chaque élément
     * @param  array<string, array<int, mixed>>  $translatedFields  champs traduits de chaque élément
     * @param  list<string>  $requiredTranslatedFields
     * @return array<string, array<int, mixed>>
     */
    protected function childRules(string $key, array $fields, array $translatedFields, array $requiredTranslatedFields = [], int $max = 50): array
    {
        /** @var list<string> $locales */
        $locales = config('translatable.locales');
        $defaultLocale = (string) config('translatable.fallback_locale');

        $rules = [
            $key => ['sometimes', 'array', "max:{$max}"],
            "{$key}.*" => ['array'],
            "{$key}.*.translations" => ['required', 'array:'.implode(',', $locales)],
            "{$key}.*.translations.{$defaultLocale}" => ['required', 'array'],
            "{$key}.*.translations.*" => ['array'],
        ];

        foreach ($fields as $field => $fieldRules) {
            $rules["{$key}.*.{$field}"] = $fieldRules;
        }

        foreach ($translatedFields as $field => $fieldRules) {
            $presence = in_array($field, $requiredTranslatedFields, true) ? ['required'] : ['nullable'];
            $rules["{$key}.*.translations.*.{$field}"] = [...$presence, ...$fieldRules];
        }

        return $rules;
    }

    /**
     * Liste d'identifiants existants d'une table (relations plusieurs-à-plusieurs).
     *
     * @return array<string, array<int, mixed>>
     */
    protected function idsRules(string $key, string $table): array
    {
        return [
            $key => ['sometimes', 'array', 'max:100'],
            "{$key}.*" => ['integer', 'distinct', Rule::exists($table, 'id')],
        ];
    }

    /**
     * Règles d'une URL publique sûre (https ou http uniquement : ni javascript:, ni data:).
     *
     * @return list<string>
     */
    protected function safeUrl(): array
    {
        return ['nullable', 'string', 'max:2048', 'url:http,https'];
    }

    /**
     * Règles des champs éditoriaux : statut, date de publication, mise en avant, position.
     * Une programmation exige une date future.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function editorialRules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(PublicationStatus::class)],
            'published_at' => ['nullable', 'date', 'required_if:status,scheduled', Rule::when($this->input('status') === 'scheduled', ['after:now'])],
            'is_featured' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:100000'],
        ];
    }

    /**
     * Slug traduit unique par langue, en ignorant les traductions du contenu modifié.
     */
    protected function uniqueTranslatedSlug(string $translationTable, string $ownerColumn, string $locale): Unique
    {
        return Rule::unique($translationTable, 'slug')
            ->where('locale', $locale)
            ->ignore($this->routeId(), $ownerColumn);
    }

    /**
     * Règles de slug (format URL) ; l'unicité est ajoutée avec uniqueTranslatedSlug().
     *
     * @return list<string>
     */
    protected function slugFormat(): array
    {
        return ['string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
    }

    /**
     * Traductions d'un contenu publié sous une URL : intitulé et slug obligatoires,
     * slug unique par langue dans la table <entité>_translations.
     *
     * @param  array<string, array<int, mixed>>  $fields  autres champs traduits
     * @param  list<string>  $requiredFields  autres champs traduits obligatoires
     * @return array<string, array<int, mixed>>
     */
    protected function sluggedTranslationRules(string $entity, array $fields = [], array $requiredFields = [], string $labelField = 'title'): array
    {
        return $this->translationRules(
            [$labelField => ['string', 'max:255'], 'slug' => $this->slugFormat(), ...$fields],
            [$labelField, 'slug', ...$requiredFields],
            fn (string $locale): array => ['slug' => [$this->uniqueTranslatedSlug("{$entity}_translations", "{$entity}_id", $locale)]],
        );
    }

    /**
     * Référence facultative à un média de la bibliothèque.
     *
     * @return list<mixed>
     */
    protected function mediaRule(): array
    {
        return ['nullable', 'integer', Rule::exists('media', 'id')];
    }

    /**
     * Code pays ISO 3166-1 alpha-2 en majuscules.
     *
     * @return list<string>
     */
    protected function countryCodeRule(): array
    {
        return ['nullable', 'string', 'regex:/^[A-Z]{2}$/'];
    }

    /**
     * Lien d'interface : URL https/http, adresse mailto: ou chemin interne (« /fr/contact »).
     *
     * @return list<string>
     */
    protected function safeLink(): array
    {
        return ['nullable', 'string', 'max:2048', 'regex:#^(https?://|mailto:|/(?!/))#i'];
    }

    /**
     * Langue gérée par le site.
     *
     * @return list<mixed>
     */
    protected function localeRule(): array
    {
        /** @var list<string> $locales */
        $locales = config('translatable.locales');

        return [...$this->requiredOnCreate(), 'string', Rule::in($locales)];
    }

    /**
     * Champs communs d'un élément ordonné et affichable.
     *
     * @return array<string, list<string>>
     */
    protected function displayRules(bool $featurable = true): array
    {
        return [
            'is_visible' => ['sometimes', 'boolean'],
            ...($featurable ? ['is_featured' => ['sometimes', 'boolean']] : []),
            'position' => ['sometimes', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
