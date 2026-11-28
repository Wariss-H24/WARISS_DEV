<?php

namespace App\Http\Controllers\Api\V1\Admin\Resume;

use App\Enums\ActivityEvent;
use App\Enums\MediaKind;
use App\Enums\MediaVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Resume\ResumeVersionRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Resume\Resume;
use App\Models\Resume\ResumeVersion;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\Media\MediaStorage;
use App\Services\Media\MediaUsages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Versions d'un CV (D15).
 *
 * Chaque envoi crée une version numérotée (1, 2, 3…) dont le PDF est rangé en privé dans la
 * bibliothèque de médias ; le site public ne le sert que par son propre téléchargement, et seulement
 * pour la version courante d'un CV public. Par défaut, la nouvelle version devient la version courante.
 */
class ResumeVersionController extends Controller
{
    public function __construct(
        private readonly MediaStorage $storage,
        private readonly MediaUsages $usages,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function store(int $id, ResumeVersionRequest $request): JsonResponse
    {
        Resume::query()->findOrFail($id);
        $data = $request->validated();
        $user = $request->user() instanceof User ? $request->user() : null;

        $media = $this->storage->store($request->file('file'), MediaVisibility::Private, MediaKind::Document, [], $user);

        try {
            $version = DB::transaction(function () use ($id, $data, $media, $user): ResumeVersion {
                /** @var Resume $resume */
                $resume = Resume::query()->lockForUpdate()->findOrFail($id);

                $version = new ResumeVersion([
                    'resume_id' => $resume->id,
                    'media_id' => $media->id,
                    'version_number' => (int) $resume->versions()->max('version_number') + 1,
                    'change_note' => $data['change_note'] ?? null,
                ]);
                $version->forceFill(['created_by' => $user?->getKey()])->save();
                $this->activityLogger->log(ActivityEvent::Created, $version);

                if ($data['make_current'] ?? true) {
                    $resume->current_version_id = $version->id;
                    $resume->save();
                    $this->activityLogger->log(ActivityEvent::Updated, $resume, ['current_version_id']);
                }

                return $version;
            });
        } catch (Throwable $exception) {
            $media->delete();
            $this->storage->deleteFile($media);

            throw $exception;
        }

        return (new AdminResource($version->load('media.translations')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(int $id, int $versionId, ResumeVersionRequest $request): AdminResource
    {
        $version = $this->find($id, $versionId);
        $version->fill($request->validated())->save();
        $this->activityLogger->log(ActivityEvent::Updated, $version, array_keys($version->getChanges()));

        return new AdminResource($version->load('media.translations'));
    }

    /**
     * Supprime une version et son fichier (s'il ne sert nulle part ailleurs).
     * La version publiée d'un CV public ne peut pas être supprimée.
     */
    public function destroy(int $id, int $versionId): Response|JsonResponse
    {
        $version = $this->find($id, $versionId);
        $resume = $version->resume;

        if ($resume->is_public && $resume->current_version_id === $version->id) {
            return response()->json([
                'message' => __('Cette version est celle que télécharge le public : choisissez une autre version courante ou rendez le CV privé avant de la supprimer.'),
            ], Response::HTTP_CONFLICT);
        }

        $media = $version->media;

        $mediaDeleted = DB::transaction(function () use ($version, $media): bool {
            $version->delete();
            $this->activityLogger->log(ActivityEvent::Deleted, $version);

            if ($this->usages->for($media) !== []) {
                return false;
            }

            $media->delete();

            return true;
        });

        if ($mediaDeleted) {
            $this->storage->deleteFile($media);
        }

        return response()->noContent();
    }

    private function find(int $resumeId, int $versionId): ResumeVersion
    {
        return ResumeVersion::query()->where('resume_id', $resumeId)->findOrFail($versionId);
    }
}
