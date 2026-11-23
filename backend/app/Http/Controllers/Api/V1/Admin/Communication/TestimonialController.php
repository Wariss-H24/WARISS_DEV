<?php

namespace App\Http\Controllers\Api\V1\Admin\Communication;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Communication\TestimonialRequest;
use App\Models\Communication\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Témoignages (D13). Statut et consentement sont fixés ici, jamais par le formulaire public.
 */
class TestimonialController extends AdminCrudController
{
    protected function model(): string
    {
        return Testimonial::class;
    }

    protected function formRequest(): string
    {
        return TestimonialRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('status'),
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
     * Statut et consentement : non assignables en masse, fixés explicitement par l'administration.
     *
     * @param  array<string, mixed>  $data
     */
    protected function beforeSave(Model $model, array $data, bool $isNew): void
    {
        foreach (['status', 'consent_given'] as $field) {
            if (array_key_exists($field, $data)) {
                $model->setAttribute($field, $data[$field]);
            }
        }
    }
}
