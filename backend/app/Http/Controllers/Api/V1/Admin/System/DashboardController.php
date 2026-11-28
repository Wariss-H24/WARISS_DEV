<?php

namespace App\Http\Controllers\Api\V1\Admin\System;

use App\Enums\MessageStatus;
use App\Enums\SubscriberStatus;
use App\Enums\TestimonialStatus;
use App\Http\Controllers\Controller;
use App\Models\Admin\ActivityLog;
use App\Models\Blog\Post;
use App\Models\Communication\Message;
use App\Models\Communication\NewsletterSubscriber;
use App\Models\Communication\Testimonial;
use App\Models\Lab\Experiment;
use App\Models\Pages\Page;
use App\Models\Portfolio\CaseStudy;
use App\Models\Portfolio\Project;
use App\Models\Services\Service;
use App\Services\Analytics\AnalyticsReport;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Tableau de bord de l'administration : état éditorial des contenus, témoignages à modérer,
 * audience des 7 derniers jours. Pour le compte admin seulement : boîte de réception, abonnés
 * et dernières actions du journal (données sensibles).
 */
class DashboardController extends Controller
{
    /**
     * @var array<string, class-string<Page|Project|CaseStudy|Service|Experiment|Post>>
     */
    private const CONTENTS = [
        'pages' => Page::class,
        'projects' => Project::class,
        'case_studies' => CaseStudy::class,
        'services' => Service::class,
        'experiments' => Experiment::class,
        'posts' => Post::class,
    ];

    public function __invoke(AnalyticsReport $report): JsonResponse
    {
        $today = CarbonImmutable::today();
        $audience = $report->build($today->subDays(6), $today, 5);

        $data = [
            'contents' => collect(self::CONTENTS)->map($this->countByStatus(...))->all(),
            'testimonials_pending' => Testimonial::query()->where('status', TestimonialStatus::Pending->value)->count(),
            'audience' => [
                'period' => $audience['period'],
                'totals' => $audience['totals'],
                'daily' => $audience['daily'],
                'top_paths' => $audience['top']['paths'],
            ],
        ];

        if (Gate::allows('view-sensitive-data')) {
            $data['inbox'] = [
                'messages_new' => Message::query()->where('status', MessageStatus::New->value)->count(),
                'messages_in_progress' => Message::query()->where('status', MessageStatus::InProgress->value)->count(),
                'subscribers' => NewsletterSubscriber::query()->where('status', SubscriberStatus::Subscribed->value)->count(),
                'subscribers_pending' => NewsletterSubscriber::query()->where('status', SubscriberStatus::Pending->value)->count(),
            ];
            $data['recent_activity'] = ActivityLog::query()
                ->with('user:id,name')
                ->latest('id')
                ->limit(10)
                ->get(['id', 'user_id', 'event', 'subject_type', 'subject_id', 'description', 'created_at'])
                ->map(fn (ActivityLog $log): array => [
                    'id' => $log->id,
                    'event' => $log->event->value,
                    'subject_type' => $log->subject_type,
                    'subject_id' => $log->subject_id,
                    'description' => $log->description,
                    'user' => $log->user?->name,
                    'created_at' => $log->created_at->toIso8601String(),
                ])
                ->all();
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Nombre de contenus par statut éditorial (hors corbeille), avec tous les statuts présents.
     *
     * @param  class-string<Page|Project|CaseStudy|Service|Experiment|Post>  $contentClass
     * @return array<string, int>
     */
    private function countByStatus(string $contentClass): array
    {
        $counts = $contentClass::query()
            ->toBase()
            ->selectRaw('status, count(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn (mixed $total): int => (int) $total);

        return [
            'draft' => $counts->get('draft', 0),
            'scheduled' => $counts->get('scheduled', 0),
            'published' => $counts->get('published', 0),
            'archived' => $counts->get('archived', 0),
        ];
    }
}
