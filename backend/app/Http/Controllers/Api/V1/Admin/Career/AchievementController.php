<?php

namespace App\Http\Controllers\Api\V1\Admin\Career;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Career\AchievementRequest;
use App\Models\Career\Achievement;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Réalisations et distinctions (D9).
 */
class AchievementController extends AdminCrudController
{
    protected function model(): string
    {
        return Achievement::class;
    }

    protected function formRequest(): string
    {
        return AchievementRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('is_visible'),
            AllowedFilter::exact('type'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'position', 'achieved_on'];
    }

    protected function defaultSort(): string
    {
        return '-achieved_on';
    }
}
