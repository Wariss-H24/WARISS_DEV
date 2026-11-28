<?php

namespace App\Http\Controllers\Api\V1\Admin\Communication;

use App\Enums\MessageStatus;
use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Communication\MessageRequest;
use App\Models\Communication\Message;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Messages reçus par le formulaire de contact (D13), donnée sensible réservée au compte admin.
 *
 * Ouvrir un message nouveau le marque comme lu. Le passage au statut « replied » date la réponse
 * (contrainte messages_replied_at_check). La suppression place le message dans la corbeille ;
 * la purge définitive est faite par la tâche planifiée au bout de 24 mois.
 */
class MessageController extends AdminCrudController
{
    protected function model(): string
    {
        return Message::class;
    }

    protected function formRequest(): string
    {
        return MessageRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('type'),
            AllowedFilter::exact('service_id'),
            AllowedFilter::callback('search', function (Builder $query, mixed $value): void {
                $term = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string) $value).'%';
                $query->where(fn (Builder $inner) => $inner
                    ->whereLike('name', $term)
                    ->orWhereLike('email', $term)
                    ->orWhereLike('subject', $term));
            }),
            AllowedFilter::trashed(),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'created_at', 'status'];
    }

    public function show(int $id): JsonResource
    {
        /** @var Message $message */
        $message = $this->find($id);
        $message->markAsRead();

        return parent::show($id);
    }

    protected function beforeSave(Model $model, array $data, bool $isNew): void
    {
        /** @var Message $model */
        if (array_key_exists('internal_note', $data)) {
            $model->internal_note = $data['internal_note'];
        }

        if (! isset($data['status'])) {
            return;
        }

        $status = MessageStatus::from($data['status']);
        $model->status = $status;
        $model->read_at ??= $status === MessageStatus::New ? null : now();

        if ($status === MessageStatus::Replied) {
            $model->replied_at ??= now();
        }
    }
}
