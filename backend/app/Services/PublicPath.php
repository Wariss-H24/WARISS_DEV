<?php

namespace App\Services;

use App\Models\Pages\Page;
use Illuminate\Database\Eloquent\Model;

/**
 * Chemin public d'un contenu sur le site (cahier des charges §21.2 bis, décision D-19).
 *
 * Sert aux redirections automatiques (D-06), au sitemap, aux URL canoniques et aux aperçus.
 * Les segments de route sont en anglais dans les deux langues ; seul le slug est traduit.
 */
class PublicPath
{
    /**
     * Segment de route de chaque type de contenu doté d'un slug.
     *
     * @var array<string, string>
     */
    private const SEGMENTS = [
        'project' => 'projects',
        'case_study' => 'case-studies',
        'service' => 'services',
        'experiment' => 'lab',
        'post' => 'blog',
        'page' => 'pages',
    ];

    /**
     * Chemin d'une page système (route fixe du frontend) d'après sa clé.
     */
    public function forSystemPage(string $key, string $locale): string
    {
        return $key === 'home' ? "/{$locale}" : "/{$locale}/{$key}";
    }

    /**
     * Chemin d'un contenu à partir de son slug, ou null si le type n'a pas de page publique.
     */
    public function forSlug(string $morphType, string $slug, string $locale): ?string
    {
        $segment = self::SEGMENTS[$morphType] ?? null;

        return $segment === null ? null : "/{$locale}/{$segment}/{$slug}";
    }

    /**
     * Chemin d'un contenu dans une langue, ou null s'il n'a pas de slug dans cette langue.
     */
    public function for(Model $content, string $locale): ?string
    {
        if ($content instanceof Page && $content->isSystem()) {
            return $this->forSystemPage($content->key, $locale);
        }

        if (! method_exists($content, 'translate')) {
            return null;
        }

        $slug = $content->translate($locale, false)?->getAttribute('slug');

        return is_string($slug) && $slug !== '' ? $this->forSlug($content->getMorphClass(), $slug, $locale) : null;
    }
}
