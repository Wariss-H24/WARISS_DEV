<?php

namespace App\Http\Controllers\Api\V1\Admin\Identity;

use App\Actions\Admin\SaveModel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Identity\ProfileRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Identity\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Profil public du propriétaire (D1) : une seule fiche, sans liste ni suppression.
 * Les réseaux sociaux se gèrent avec la ressource social-links.
 */
class ProfileController extends Controller
{
    public function show(): AdminResource
    {
        return new AdminResource($this->profile() ?? abort(Response::HTTP_NOT_FOUND, __('Le profil n\'a pas encore été rempli.')));
    }

    /**
     * Crée le profil (201) au premier enregistrement, puis le modifie (200).
     */
    public function update(ProfileRequest $request, SaveModel $saveModel): JsonResponse
    {
        $profile = $this->profile() ?? new Profile;
        $isNew = ! $profile->exists;
        $user = $request->user();

        $saveModel->handle($profile, $request->validated(), beforeSave: function (Model $model, array $data, bool $isNew) use ($user): void {
            if ($isNew && $user instanceof User) {
                $model->setAttribute('user_id', $user->id);
            }
        });

        return (new AdminResource($this->profile()))
            ->response()
            ->setStatusCode($isNew ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    private function profile(): ?Profile
    {
        return Profile::query()->with(['translations', 'socialLinks'])->oldest('id')->first();
    }
}
