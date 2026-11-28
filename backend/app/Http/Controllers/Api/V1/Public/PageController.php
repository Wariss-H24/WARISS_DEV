<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Resources\Public\PublicVisibility;
use App\Models\Pages\Page;
use App\Models\Pages\PageSection;
use App\Models\Pages\PageSectionItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * Page publiée (D18) avec ses sections actives, dans l'ordre. Une page système se demande par sa
 * clé (home, about, legal-notice…), une page personnalisée par son slug traduit.
 * Les contenus mis en avant dans une section n'apparaissent que s'ils sont eux-mêmes publics.
 */
class PageController extends PublicController
{
    public function show(string $page): JsonResponse
    {
        $found = $this->query()
            ->published()
            ->where(fn (Builder $query) => $query->where('key', $page)->orWhereTranslation('slug', $page))
            ->firstOrFail();

        return $this->respond($found);
    }

    /**
     * Aperçu d'une page quel que soit son statut (lien signé vérifié par PreviewController).
     */
    public function preview(int $id): JsonResponse
    {
        return $this->respond($this->query()->findOrFail($id), ['is_preview' => true]);
    }

    /**
     * @return Builder<Page>
     */
    private function query(): Builder
    {
        return Page::query()->with([
            'translations',
            'sections' => fn ($query) => $query->where('is_active', true)->orderBy('position')->orderBy('id')->with(['translations', 'items.itemable.translations']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function respond(Page $page, array $extra = []): JsonResponse
    {
        $page->sections->each(function (PageSection $section): void {
            $section->setRelation('items', $section->items
                ->filter(fn (PageSectionItem $item): bool => PublicVisibility::allows($item->itemable))
                ->values());
        });

        return $this->item($page, ['seo' => $this->seo($page), ...$extra]);
    }
}
