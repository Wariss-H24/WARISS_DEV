<?php

namespace App\Http\Controllers\Api\V1\Admin\Catalog;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Catalog\SkillRequest;
use App\Models\Catalog\Skill;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Compétences (D5), liées aux technologies du catalogue.
 */
class SkillController extends AdminCrudController
{
    protected function model(): string
    {
        return Skill::class;
    }

    protected function formRequest(): string
    {
        return SkillRequest::class;
    }

    protected function relations(): array
    {
        return ['technology_ids' => 'technologies'];
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('skill_category_id'),
            AllowedFilter::exact('is_visible'),
            AllowedFilter::exact('is_featured'),
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
}
