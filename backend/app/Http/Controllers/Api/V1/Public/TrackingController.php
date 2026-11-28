<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\AnalyticsEventName;
use App\Enums\ShareNetwork;
use App\Http\Controllers\Controller;
use App\Services\Analytics\AudienceTracker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * Collecte de la mesure d'audience depuis le navigateur du visiteur (D17, D-16), sans cookie.
 * Répond toujours 204 : un visiteur ignoré (robot, DNT, administration) ne voit aucune différence.
 * Les événements envoyés par le serveur lui-même (téléchargement du CV, contact, newsletter) ne
 * peuvent pas être déclarés ici.
 */
class TrackingController extends Controller
{
    /**
     * Contenus qu'une page vue ou un événement peut désigner (alias de la morph map).
     *
     * @var list<string>
     */
    private const CONTENT_TYPES = ['page', 'project', 'case_study', 'service', 'experiment', 'post'];

    /**
     * Événements que le navigateur peut déclarer.
     *
     * @var list<AnalyticsEventName>
     */
    private const CLIENT_EVENTS = [AnalyticsEventName::CtaClick, AnalyticsEventName::DemoOpen, AnalyticsEventName::OutboundClick, AnalyticsEventName::Share];

    public function __construct(private readonly AudienceTracker $tracker) {}

    public function view(Request $request): Response
    {
        $data = $request->validate([
            'path' => $this->pathRule(),
            'locale' => ['required', 'string', Rule::in((array) config('translatable.locales'))],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'utm_source' => ['nullable', 'string', 'max:100'],
            'utm_medium' => ['nullable', 'string', 'max:100'],
            'utm_campaign' => ['nullable', 'string', 'max:100'],
            ...$this->contentRules(),
        ]);

        if ($this->tracker->shouldTrack($request)) {
            $this->tracker->recordView($request, $data, $this->content($data));
        }

        return response()->noContent();
    }

    public function event(Request $request): Response
    {
        $data = $request->validate([
            'name' => ['required', Rule::in(array_map(fn (AnalyticsEventName $name): string => $name->value, self::CLIENT_EVENTS))],
            'path' => $this->pathRule(),
            'network' => ['required_if:name,share', 'prohibited_unless:name,share', Rule::enum(ShareNetwork::class)],
            'target' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9._:\/-]+$/'],
            ...$this->contentRules(),
        ]);

        $properties = array_filter([
            'network' => $data['network'] ?? null,
            'target' => isset($data['target']) ? mb_substr((string) (parse_url((string) $data['target'], PHP_URL_HOST) ?: $data['target']), 0, 255) : null,
        ], fn (?string $value): bool => $value !== null);

        $this->tracker->recordEvent($request, AnalyticsEventName::from($data['name']), $data['path'], $this->content($data), $properties);

        return response()->noContent();
    }

    /**
     * @return list<string>
     */
    private function pathRule(): array
    {
        return ['required', 'string', 'max:2048', 'regex:#^/(?!/)#'];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function contentRules(): array
    {
        return [
            'content_type' => ['nullable', 'required_with:content_id', Rule::in(self::CONTENT_TYPES)],
            'content_id' => ['nullable', 'required_with:content_type', 'integer', 'min:1'],
        ];
    }

    /**
     * Contenu désigné, s'il existe (sinon la mesure est gardée sans contenu).
     *
     * @param  array<string, mixed>  $data
     */
    private function content(array $data): ?Model
    {
        if (! isset($data['content_type'], $data['content_id'])) {
            return null;
        }

        /** @var class-string<Model>|null $modelClass */
        $modelClass = Relation::getMorphedModel((string) $data['content_type']);

        return $modelClass === null ? null : $modelClass::query()->find((int) $data['content_id']);
    }
}
