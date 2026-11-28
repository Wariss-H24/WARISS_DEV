<?php

namespace App\Services\Analytics;

use App\Enums\AnalyticsEventName;
use App\Enums\DeviceType;
use App\Enums\ReferrerType;
use App\Models\Analytics\AnalyticsEvent;
use App\Models\Analytics\PageView;
use DeviceDetector\DeviceDetector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Mesure d'audience sans cookie ni donnée personnelle (D17, D-16).
 *
 * - Empreinte de session : HMAC-SHA256 de l'IP et du navigateur avec un sel qui change chaque jour
 *   (dérivé de APP_KEY). L'IP n'est jamais stockée ; d'un jour à l'autre, un même visiteur n'est
 *   plus reconnaissable.
 * - Ignorés : robots (Device Detector), comptes de l'administration connectés, et visiteurs qui
 *   demandent à ne pas être suivis (en-têtes DNT: 1 ou Sec-GPC: 1).
 * - Pays : en-tête CF-IPCountry fourni par Cloudflare, s'il est présent.
 */
class AudienceTracker
{
    /**
     * @var list<string>
     */
    private const SEARCH_ENGINES = ['google.', 'bing.com', 'duckduckgo.com', 'qwant.com', 'ecosia.org', 'search.yahoo.', 'yandex.', 'baidu.com', 'search.brave.com', 'startpage.com'];

    /**
     * @var list<string>
     */
    private const SOCIAL_NETWORKS = ['linkedin.com', 'lnkd.in', 'twitter.com', 'x.com', 't.co', 'facebook.com', 'fb.com', 'm.facebook.com', 'l.facebook.com', 'instagram.com', 'whatsapp.com', 'wa.me', 'reddit.com', 'youtube.com', 't.me', 'telegram.org', 'threads.net', 'bsky.app', 'mastodon.social'];

    /**
     * @var array<string, DeviceDetector>
     */
    private array $detectors = [];

    public function shouldTrack(Request $request): bool
    {
        if ($request->header('DNT') === '1' || $request->header('Sec-GPC') === '1') {
            return false;
        }

        if ($request->user('sanctum') !== null) {
            return false;
        }

        $userAgent = (string) $request->userAgent();

        return $userAgent !== '' && ! $this->detector($userAgent)->isBot();
    }

    /**
     * @param  array{path: string, locale: string, referrer?: string|null, utm_source?: string|null, utm_medium?: string|null, utm_campaign?: string|null}  $data
     */
    public function recordView(Request $request, array $data, ?Model $content = null): void
    {
        [$referrerHost, $referrerType] = $this->referrer($data['referrer'] ?? null);

        PageView::query()->create([
            'viewed_at' => now(),
            'path' => self::cleanPath($data['path']),
            'locale' => $data['locale'],
            'viewable_type' => $content?->getMorphClass(),
            'viewable_id' => $content?->getKey(),
            'session_hash' => $this->sessionHash($request),
            'referrer_host' => $referrerHost,
            'referrer_type' => $referrerType,
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'device_type' => $this->deviceType((string) $request->userAgent()),
            'country_code' => $this->country($request),
        ]);
    }

    /**
     * @param  array<string, string>  $properties
     */
    public function recordEvent(Request $request, AnalyticsEventName $name, string $path, ?Model $content = null, array $properties = []): void
    {
        if (! $this->shouldTrack($request)) {
            return;
        }

        AnalyticsEvent::query()->create([
            'occurred_at' => now(),
            'name' => $name,
            'path' => self::cleanPath($path),
            'eventable_type' => $content?->getMorphClass(),
            'eventable_id' => $content?->getKey(),
            'session_hash' => $this->sessionHash($request),
            'properties' => $properties,
        ]);
    }

    /**
     * Chemin sans requête ni ancre : une adresse peut contenir des données personnelles (?email=…).
     */
    public static function cleanPath(string $path): string
    {
        $clean = (string) strtok($path, '?#');

        return str_starts_with($clean, '/') ? mb_substr($clean, 0, 2048) : '/';
    }

    public function sessionHash(Request $request): string
    {
        $dailySalt = hash_hmac('sha256', 'audience:'.now()->toDateString(), (string) config('app.key'));

        return hash_hmac('sha256', ((string) $request->ip()).'|'.((string) $request->userAgent()), $dailySalt);
    }

    /**
     * Hôte et nature de la provenance ; une provenance interne au site compte comme directe.
     *
     * @return array{0: string|null, 1: ReferrerType}
     */
    public function referrer(?string $referrer): array
    {
        $host = is_string($referrer) ? parse_url($referrer, PHP_URL_HOST) : null;

        if (! is_string($host) || $host === '') {
            return [null, ReferrerType::Direct];
        }

        $host = strtolower((string) preg_replace('/^www\./', '', $host));
        $siteHost = strtolower((string) preg_replace('/^www\./', '', (string) parse_url((string) config('wariss.site_url'), PHP_URL_HOST)));

        if ($host === $siteHost) {
            return [null, ReferrerType::Direct];
        }

        $matches = fn (array $domains): bool => collect($domains)->contains(fn (string $domain): bool => str_starts_with($host, $domain) || $host === $domain || str_ends_with($host, '.'.$domain));

        return [mb_substr($host, 0, 255), match (true) {
            $matches(self::SEARCH_ENGINES) => ReferrerType::Search,
            $matches(self::SOCIAL_NETWORKS) => ReferrerType::Social,
            default => ReferrerType::Other,
        }];
    }

    private function deviceType(string $userAgent): DeviceType
    {
        $detector = $this->detector($userAgent);

        return match (true) {
            $detector->isTablet() => DeviceType::Tablet,
            $detector->isMobile() => DeviceType::Mobile,
            default => DeviceType::Desktop,
        };
    }

    private function country(Request $request): ?string
    {
        $country = strtoupper((string) $request->header('CF-IPCountry'));

        return preg_match('/^[A-Z]{2}$/', $country) === 1 && ! in_array($country, ['XX', 'T1'], true) ? $country : null;
    }

    /**
     * Analyse du navigateur, faite une seule fois par requête (elle est coûteuse).
     */
    private function detector(string $userAgent): DeviceDetector
    {
        if (! isset($this->detectors[$userAgent])) {
            $detector = new DeviceDetector($userAgent);
            $detector->discardBotInformation();
            $detector->parse();
            $this->detectors = [$userAgent => $detector];
        }

        return $this->detectors[$userAgent];
    }
}
