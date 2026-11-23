<?php

namespace App\Http\Controllers\Api\V1\Admin\Career;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Career\TimelineEventRequest;
use App\Models\Career\TimelineEvent;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Événements de la chronologie (D10).
 */
class TimelineEventController extends AdminCrudController
{
    protected function model(): string
    {
        return TimelineEvent::class;
    }

    protected function formRequest(): string
    {
        return TimelineEventRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('type'),
            AllowedFilter::exact('is_visible'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'position', 'event_date'];
    }

    protected function defaultSort(): string
    {
        return '-event_date';
    }
}
