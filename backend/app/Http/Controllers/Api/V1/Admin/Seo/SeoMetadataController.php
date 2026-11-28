<?php

namespace App\Http\Controllers\Api\V1\Admin\Seo;

use App\Actions\Admin\SaveModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Seo\SeoMetadataRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Seo\SeoMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Fiche SEO d'un contenu public (D16), une par contenu : /admin/seo/{type}/{id}.
 * Les types sont les alias de la morph map autorisés par seo_metadata_seoable_type_check.
 */
class SeoMetadataController extends Controller
{
    /**
     * @var list<string>
     */
    public const TYPES = ['page', 'service', 'project', 'case_study', 'experiment', 'post'];

    /**
     * Fiche SEO du contenu ; « data » vaut null tant qu'aucune n'a été enregistrée.
     */
    public function show(string $type, int $id): JsonResponse
    {
        $seo = $this->content($type, $id)->getRelationValue('seo');

        return response()->json([
            'data' => $seo instanceof SeoMetadata ? AdminResource::payload($seo->load('translations')) : null,
        ]);
    }

    /**
     * Crée ou modifie la fiche SEO du contenu.
     */
    public function update(string $type, int $id, SeoMetadataRequest $request, SaveModel $saveModel): JsonResponse
    {
        $content = $this->content($type, $id);
        $seo = $content->getRelationValue('seo');
        $isNew = ! $seo instanceof SeoMetadata;

        $seo = $saveModel->handle($isNew ? new SeoMetadata : $seo, $request->validated(), beforeSave: function (Model $model, array $data, bool $isNew) use ($content): void {
            if ($isNew) {
                $model->setAttribute('seoable_type', $content->getMorphClass());
                $model->setAttribute('seoable_id', $content->getKey());
            }
        });

        return response()->json(
            ['data' => AdminResource::payload($seo->refresh()->load('translations'))],
            $isNew ? Response::HTTP_CREATED : Response::HTTP_OK,
        );
    }

    /**
     * Contenu visé, y compris dans la corbeille (sa fiche SEO y est conservée).
     */
    private function content(string $type, int $id): Model
    {
        /** @var class-string<Model>|null $modelClass */
        $modelClass = Relation::getMorphedModel($type);
        abort_if($modelClass === null || ! in_array($type, self::TYPES, true), Response::HTTP_NOT_FOUND);

        return $modelClass::query()->withoutGlobalScope(SoftDeletingScope::class)->with('seo.translations')->findOrFail($id);
    }
}
