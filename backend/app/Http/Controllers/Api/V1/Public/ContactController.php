<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\AnalyticsEventName;
use App\Enums\MessageType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ContactRequest;
use App\Models\Communication\Message;
use App\Models\Services\Service;
use App\Models\User;
use App\Notifications\NewContactMessage;
use App\Services\Analytics\AudienceTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Formulaire de contact et de devis (D13) : le message est enregistré (IP réduite à une empreinte),
 * le compte admin est prévenu par e-mail et l'envoi est compté (contact_submit).
 */
class ContactController extends Controller
{
    public function __invoke(ContactRequest $request, AudienceTracker $tracker): JsonResponse
    {
        $accepted = response()->json(['message' => __('Merci, votre message a bien été envoyé. Je vous réponds au plus vite.')], Response::HTTP_ACCEPTED);

        if ($request->isSpam()) {
            return $accepted;
        }

        $data = $request->validated();
        $service = isset($data['service'])
            ? Service::query()->published()->whereTranslation('slug', $data['service'])->first()
            : null;

        DB::transaction(function () use ($data, $service, $request): void {
            $message = new Message([
                'type' => $data['type'] ?? MessageType::Contact->value,
                'service_id' => $service?->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'] ?? null,
                'body' => $data['body'],
                'details' => array_filter($data['details'] ?? [], fn (?string $value): bool => $value !== null && $value !== ''),
                'locale' => app()->getLocale(),
            ]);
            $message->forceFill(['consent_given' => true, 'consented_at' => now(), 'ip_hash' => $request->ipHash()])->save();

            Notification::send(
                User::query()->where('role', UserRole::Admin->value)->where('is_active', true)->get(),
                new NewContactMessage($message),
            );
        });

        $tracker->recordEvent($request, AnalyticsEventName::ContactSubmit, '/'.app()->getLocale().'/contact', $service);

        return $accepted;
    }
}
