<?php

namespace App\Http\Controllers\Api\V1\Admin\Media;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Media\MediaCollectionRequest;
use App\Models\Media\MediaCollection;

/**
 * Collections de la bibliothèque de médias (D14).
 */
class MediaCollectionController extends AdminCrudController
{
    protected function model(): string
    {
        return MediaCollection::class;
    }

    protected function formRequest(): string
    {
        return MediaCollectionRequest::class;
    }

    protected function sorts(): array
    {
        return ['id', 'position', 'created_at', 'updated_at'];
    }

    protected function defaultSort(): string
    {
        return 'position';
    }
}
