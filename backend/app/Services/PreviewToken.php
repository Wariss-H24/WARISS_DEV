<?php

namespace App\Services;

/**
 * Jeton d'aperçu d'un brouillon (D-09) : « type.id.langue.expiration.signature », signé par
 * HMAC-SHA256 avec APP_KEY et valable 30 minutes. Autonome (aucune table) : il ne peut être ni
 * modifié ni prolongé sans invalider la signature, et expire tout seul.
 */
class PreviewToken
{
    public const LIFETIME_MINUTES = 30;

    public function issue(string $type, int $id, string $locale): string
    {
        $payload = implode('.', [$type, $id, $locale, now()->addMinutes(self::LIFETIME_MINUTES)->getTimestamp()]);

        return $payload.'.'.$this->sign($payload);
    }

    /**
     * Contenu désigné par un jeton valide et non expiré, sinon null.
     *
     * @return array{type: string, id: int, locale: string, expires_at: int}|null
     */
    public function verify(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 5) {
            return null;
        }

        [$type, $id, $locale, $expiresAt, $signature] = $parts;

        if (! hash_equals($this->sign(implode('.', [$type, $id, $locale, $expiresAt])), $signature)
            || ! ctype_digit($id) || ! ctype_digit($expiresAt) || (int) $expiresAt < now()->getTimestamp()) {
            return null;
        }

        return ['type' => $type, 'id' => (int) $id, 'locale' => $locale, 'expires_at' => (int) $expiresAt];
    }

    private function sign(string $payload): string
    {
        return hash_hmac('sha256', 'preview:'.$payload, (string) config('app.key'));
    }
}
