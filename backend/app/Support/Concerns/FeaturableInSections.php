<?php

namespace App\Support\Concerns;

use App\Models\Pages\PageSectionItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Contenu pouvant être mis en avant dans une section de page (page_section_items, D18) :
 * projets, services, articles, compétences, certifications.
 *
 * Les mises en avant sont retirées à la suppression définitive du contenu, pour qu'aucune
 * section ne pointe vers un contenu disparu ; elles sont conservées lors d'une suppression douce.
 */
trait FeaturableInSections
{
    /**
     * Retire les mises en avant à la suppression définitive du contenu.
     */
    protected static function bootFeaturableInSections(): void
    {
        static::deleted(function (Model $content): void {
            if (method_exists($content, 'isForceDeleting') && ! $content->isForceDeleting()) {
                return;
            }

            PageSectionItem::query()
                ->where('itemable_type', $content->getMorphClass())
                ->where('itemable_id', $content->getKey())
                ->delete();
        });
    }

    /**
     * Sections dans lesquelles ce contenu est mis en avant.
     *
     * @return MorphMany<PageSectionItem, $this>
     */
    public function sectionFeatures(): MorphMany
    {
        return $this->morphMany(PageSectionItem::class, 'itemable');
    }
}
