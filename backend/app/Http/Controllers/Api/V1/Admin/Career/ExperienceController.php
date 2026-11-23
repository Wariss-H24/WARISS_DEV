<?php

namespace App\Http\Controllers\Api\V1\Admin\Career;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Career\ExperienceRequest;
use App\Models\Career\Experience;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Expériences professionnelles (D8).
 */
class ExperienceController extends AdminCrudController
{
    protected function model(): string
    {
        return Experience::class;
    }

    protected function formRequest(): string
    {
        return ExperienceRequest::class;
    }

    protected function relations(): array
    {
        return ['technology_ids' => 'technologies'];
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('is_visible'),
            AllowedFilter::exact('employment_type'),
            AllowedFilter::trashed(),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'position', 'started_on', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return '-started_on';
    }

    /**
     * Une expérience marquée « en cours » perd sa date de fin.
     *
     * @param  array<string, mixed>  $data
     */
    protected function beforeSave(Model $model, array $data, bool $isNew): void
    {
        if ($model->getAttribute('is_current') === true) {
            $model->setAttribute('ended_on', null);
        }
    }
}
