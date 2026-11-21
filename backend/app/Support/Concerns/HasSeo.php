<?php

namespace App\Support\Concerns;

use App\Models\Seo\SeoMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Contenu public doté de métadonnées SEO (domaine D16).
 *
 * La fiche SEO est supprimée avec le contenu lors d'une suppression définitive (une relation
 * polymorphe n'a pas de clé étrangère) ; elle est conservée lors d'une suppression douce.
 * Le modèle doit figurer dans la morph map et dans seo_metadata_seoable_type_check.
 */
trait HasSeo
{
    /**
     * Supprime la fiche SEO à la suppression définitive du contenu.
     */
    protected static function bootHasSeo(): void
    {
        static::deleted(function (Model $content): void {
            if (method_exists($content, 'isForceDeleting') && ! $content->isForceDeleting()) {
                return;
            }

            SeoMetadata::query()
                ->where('seoable_type', $content->getMorphClass())
                ->where('seoable_id', $content->getKey())
                ->delete();
        });
    }

    /**
     * @return MorphOne<SeoMetadata, $this>
     */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }
}
