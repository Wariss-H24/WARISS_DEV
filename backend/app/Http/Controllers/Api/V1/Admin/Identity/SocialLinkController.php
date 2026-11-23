<?php

namespace App\Http\Controllers\Api\V1\Admin\Identity;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Identity\SocialLinkRequest;
use App\Models\Identity\Profile;
use App\Models\Identity\SocialLink;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Liens vers les réseaux sociaux du profil (D1).
 */
class SocialLinkController extends AdminCrudController
{
    protected function model(): string
    {
        return SocialLink::class;
    }

    protected function formRequest(): string
    {
        return SocialLinkRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('is_visible'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'position', 'created_at', 'updated_at'];
    }

    protected function defaultSort(): string
    {
        return 'position';
    }

    /**
     * Un lien appartient toujours au profil du propriétaire, qui doit exister.
     *
     * @param  array<string, mixed>  $data
     */
    protected function beforeSave(Model $model, array $data, bool $isNew): void
    {
        if (! $isNew) {
            return;
        }

        $profileId = Profile::query()->value('id');

        if ($profileId === null) {
            throw new HttpException(422, __('Créez d\'abord le profil avant d\'ajouter des liens.'));
        }

        $model->setAttribute('profile_id', $profileId);
    }
}
