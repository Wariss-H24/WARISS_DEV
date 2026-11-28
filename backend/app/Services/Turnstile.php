<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Vérification Cloudflare Turnstile côté serveur (formulaires publics, architecture §3.6).
 *
 * Sans clé secrète configurée : vérification désactivée en local et en test, refusée en production
 * (on ne laisse jamais un formulaire public sans protection par erreur de configuration).
 * Si Cloudflare ne répond pas, le formulaire est refusé.
 */
class Turnstile
{
    public function verify(?string $token, ?string $ip): bool
    {
        $secret = config('services.turnstile.secret_key');

        if (! is_string($secret) || $secret === '') {
            return ! app()->isProduction();
        }

        if (! is_string($token) || $token === '' || strlen($token) > 2048) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post((string) config('services.turnstile.verify_url'), [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return $response->successful() && $response->json('success') === true;
        } catch (Throwable $exception) {
            Log::warning('Vérification Turnstile impossible', ['error' => $exception->getMessage()]);

            return false;
        }
    }
}
