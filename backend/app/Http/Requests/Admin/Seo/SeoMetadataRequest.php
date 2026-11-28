<?php

namespace App\Http\Requests\Admin\Seo;

use App\Http\Requests\Admin\AdminFormRequest;
use Closure;
use Illuminate\Validation\Rule;

/**
 * Fiche SEO d'un contenu public (D16) : indexation, URL canonique, image de partage,
 * données structurées schema.org et textes par langue (longueurs conseillées par les moteurs).
 */
class SeoMetadataRequest extends AdminFormRequest
{
    /**
     * Types schema.org proposés pour les contenus du site.
     *
     * @var list<string>
     */
    public const SCHEMA_TYPES = ['WebPage', 'AboutPage', 'ContactPage', 'CollectionPage', 'Article', 'BlogPosting', 'TechArticle', 'CreativeWork', 'SoftwareSourceCode', 'Service', 'ProfilePage'];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'robots_index' => ['sometimes', 'boolean'],
            'robots_follow' => ['sometimes', 'boolean'],
            'canonical_url' => $this->safeUrl(),
            'og_image_media_id' => $this->mediaRule(),
            'schema_type' => ['nullable', 'string', Rule::in(self::SCHEMA_TYPES)],
            'structured_data' => ['nullable', 'array', $this->jsonObject(...)],
            ...$this->translationRules([
                'meta_title' => ['string', 'max:70'],
                'meta_description' => ['string', 'max:200'],
                'og_title' => ['string', 'max:100'],
                'og_description' => ['string', 'max:200'],
            ]),
        ];
    }

    /**
     * Les données structurées sont un objet JSON-LD (pas une liste) de taille raisonnable.
     */
    private function jsonObject(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        if (array_is_list($value) && $value !== []) {
            $fail(__('Les données structurées doivent être un objet JSON.'));
        } elseif (strlen((string) json_encode($value)) > 10_000) {
            $fail(__('Les données structurées ne doivent pas dépasser 10 000 caractères.'));
        }
    }
}
