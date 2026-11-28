<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\AnalyticsEventName;
use App\Enums\SubscriberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\NewsletterSubscribeRequest;
use App\Models\Communication\NewsletterSubscriber;
use App\Notifications\ConfirmNewsletterSubscription;
use App\Services\Analytics\AudienceTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Newsletter (D13), en double opt-in.
 *
 * - Inscription : réponse identique que l'adresse soit nouvelle, déjà inscrite ou désinscrite
 *   (personne ne peut savoir qui est abonné) ; un e-mail de confirmation part si nécessaire.
 * - Confirmation : jeton à usage unique, valable 7 jours, dont la base ne garde que l'empreinte.
 * - Désinscription : lien signé présent dans chaque e-mail, sans compte ni mot de passe.
 */
class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request, AudienceTracker $tracker): JsonResponse
    {
        $accepted = response()->json(
            ['message' => __('Merci ! Si cette adresse est valide, vous allez recevoir un e-mail pour confirmer votre inscription.')],
            Response::HTTP_ACCEPTED,
        );

        if ($request->isSpam()) {
            return $accepted;
        }

        $data = $request->validated();
        $email = mb_strtolower(trim($data['email']));
        $locale = app()->getLocale();

        DB::transaction(function () use ($email, $locale, $data, $request): void {
            $subscriber = NewsletterSubscriber::query()->whereRaw('lower(email) = ?', [$email])->lockForUpdate()->first();

            if ($subscriber?->status === SubscriberStatus::Subscribed) {
                return;
            }

            $subscriber ??= new NewsletterSubscriber(['email' => $email]);
            $subscriber->fill(['locale' => $locale, 'consent_source' => $data['source'] ?? 'website_form']);
            $subscriber->forceFill([
                'status' => SubscriberStatus::Pending,
                'consented_at' => now(),
                'unsubscribed_at' => null,
                'ip_hash' => $request->ipHash(),
            ]);

            $token = $subscriber->issueConfirmationToken();

            Notification::route('mail', $email)->notify(new ConfirmNewsletterSubscription($token, $locale));
        });

        $tracker->recordEvent($request, AnalyticsEventName::NewsletterSubscribe, "/{$locale}/newsletter");

        return $accepted;
    }

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate(['token' => ['required', 'string', 'size:64']]);

        $subscriber = NewsletterSubscriber::query()
            ->withConfirmationToken($validated['token'])
            ->where('status', SubscriberStatus::Pending->value)
            ->where('updated_at', '>=', now()->subDays((int) config('wariss.newsletter.confirmation_days')))
            ->first();

        abort_if($subscriber === null, Response::HTTP_NOT_FOUND, __('Ce lien de confirmation est invalide ou a expiré.'));

        $subscriber->confirm();

        return response()->json(['message' => __('Votre inscription à la newsletter est confirmée. Merci !')]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
            'token' => ['required', 'string', 'size:64'],
        ]);

        $subscriber = NewsletterSubscriber::query()->find($validated['id']);

        abort_if(
            $subscriber === null || ! hash_equals($subscriber->unsubscribeToken(), $validated['token']),
            Response::HTTP_NOT_FOUND,
            __('Ce lien de désinscription est invalide.'),
        );

        if ($subscriber->status !== SubscriberStatus::Unsubscribed) {
            $subscriber->unsubscribe();
        }

        return response()->json(['message' => __('Vous êtes désinscrit de la newsletter. Vous ne recevrez plus d\'e-mails.')]);
    }
}
