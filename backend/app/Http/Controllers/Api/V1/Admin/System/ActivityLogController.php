<?php

namespace App\Http\Controllers\Api\V1\Admin\System;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Models\Admin\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Journal d'activité (D16), en lecture seule et réservé au compte admin.
 * Il est purgé automatiquement au bout de 12 mois.
 */
class ActivityLogController extends AdminCrudController
{
    protected function model(): string
    {
        return ActivityLog::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('event'),
            AllowedFilter::exact('subject_type'),
            AllowedFilter::exact('subject_id'),
            AllowedFilter::exact('user_id'),
            AllowedFilter::callback('from', fn (Builder $query, mixed $value) => $query->where('created_at', '>=', (string) $value)),
            AllowedFilter::callback('to', fn (Builder $query, mixed $value) => $query->where('created_at', '<=', (string) $value)),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'created_at'];
    }

    protected function detailRelations(): array
    {
        return ['user:id,name,role'];
    }

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()->with('user:id,name,role');
    }
}
