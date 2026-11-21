<?php

namespace App\Support\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Recherche plein texte dans les traductions (décision D-08).
 *
 * La table de traduction doit porter une colonne générée search_vector, construite avec la
 * configuration linguistique de la ligne (french / english) et immutable_unaccent().
 * La requête est analysée avec la même configuration et sans accents, en syntaxe « moteur
 * de recherche » : mots, "phrase exacte", -exclusion, or.
 */
trait SearchableTranslations
{
    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function search(Builder $query, string $term, ?string $locale = null): void
    {
        $locale ??= app()->getLocale();

        $query->whereHas('translations', function (Builder $translations) use ($term, $locale): void {
            $translations->where('locale', $locale)->whereRaw(
                "search_vector @@ websearch_to_tsquery(CASE ? WHEN 'fr' THEN 'french'::regconfig ELSE 'english'::regconfig END, immutable_unaccent(?))",
                [$locale, $term],
            );
        });
    }
}
