<?php

namespace App\Http\Controllers\Api\V1\Admin\Communication;

use App\Enums\ActivityEvent;
use App\Enums\SubscriberStatus;
use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Models\Communication\NewsletterSubscriber;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Abonnés à la newsletter (D13), donnée sensible réservée au compte admin.
 *
 * Les abonnements naissent uniquement du formulaire public (double confirmation) : l'administration
 * ne crée ni ne modifie une adresse. Elle peut désabonner une adresse, ou l'effacer définitivement
 * (droit à l'effacement), ce qui supprime aussi son historique d'envois.
 */
class NewsletterSubscriberController extends AdminCrudController
{
    protected function model(): string
    {
        return NewsletterSubscriber::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('locale'),
            AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
                $query->whereLike('email', '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string) $value).'%');
            }),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'created_at', 'confirmed_at', 'email'];
    }

    public function unsubscribe(int $id, ActivityLogger $activityLogger): JsonResource
    {
        /** @var NewsletterSubscriber $subscriber */
        $subscriber = $this->find($id);

        if ($subscriber->status !== SubscriberStatus::Unsubscribed) {
            $subscriber->unsubscribe();
            $activityLogger->log(ActivityEvent::Updated, $subscriber, ['status']);
        }

        return $this->resource($subscriber->refresh());
    }
}
