<?php

namespace App\Http\Requests\Public;

use App\Services\Turnstile;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Base des formulaires publics (contact, newsletter) : jeton Cloudflare Turnstile vérifié côté
 * serveur et champ piège « website », invisible pour un humain. Un robot qui remplit le piège reçoit
 * la même réponse qu'un visiteur, mais rien n'est enregistré (isSpam()).
 */
abstract class PublicFormRequest extends FormRequest
{
    public const HONEYPOT_FIELD = 'website';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function protectionRules(): array
    {
        return [
            'turnstile_token' => ['nullable', 'string', 'max:2048'],
            self::HONEYPOT_FIELD => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return list<Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->isSpam()) {
                    return;
                }

                if (! app(Turnstile::class)->verify($this->input('turnstile_token'), $this->ip())) {
                    $validator->errors()->add('turnstile_token', __('La vérification anti-robot a échoué. Merci de réessayer.'));
                }
            },
        ];
    }

    /**
     * Le champ piège a été rempli : envoi automatique, à ignorer sans le signaler.
     */
    public function isSpam(): bool
    {
        return $this->filled(self::HONEYPOT_FIELD);
    }

    /**
     * Empreinte de l'adresse IP (jamais stockée en clair).
     */
    public function ipHash(): ?string
    {
        $ip = $this->ip();

        return $ip === null ? null : hash_hmac('sha256', $ip, (string) config('app.key'));
    }
}
