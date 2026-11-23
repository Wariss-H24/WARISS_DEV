<?php

namespace App\Http\Controllers\Api\V1\Admin\Communication;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Communication\NewsletterCampaignRequest;
use App\Models\Communication\NewsletterCampaign;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Campagnes de newsletter (D13) : rédaction seulement, l'envoi est prévu après la V1.
 */
class NewsletterCampaignController extends AdminCrudController
{
    protected function model(): string
    {
        return NewsletterCampaign::class;
    }

    protected function formRequest(): string
    {
        return NewsletterCampaignRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('locale'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'scheduled_at', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return '-id';
    }

    /**
     * Le créateur est le compte connecté.
     *
     * @param  array<string, mixed>  $data
     */
    protected function beforeSave(Model $model, array $data, bool $isNew): void
    {
        if ($isNew) {
            $model->setAttribute('created_by', request()->user()?->getKey());
        }
    }
}
