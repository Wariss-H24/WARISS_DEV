<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\TestimonialStatus;
use App\Http\Resources\Public\PublicVisibility;
use App\Models\Career\Achievement;
use App\Models\Career\Certification;
use App\Models\Career\Education;
use App\Models\Career\Experience;
use App\Models\Career\TimelineEvent;
use App\Models\Communication\Testimonial;
use Illuminate\Http\JsonResponse;

/**
 * Parcours public (D8, D9, D10) et témoignages approuvés (D13), éléments visibles uniquement,
 * du plus récent au plus ancien.
 */
class CareerController extends PublicController
{
    public function experiences(): JsonResponse
    {
        return $this->list(Experience::query()
            ->where('is_visible', true)
            ->with(['translations', 'technologies' => fn ($query) => $query->where('is_visible', true)])
            ->orderByDesc('is_current')
            ->orderByDesc('started_on')
            ->orderBy('position')
            ->get());
    }

    public function educations(): JsonResponse
    {
        return $this->list(Education::query()
            ->where('is_visible', true)
            ->with('translations')
            ->orderByRaw('started_on DESC NULLS LAST')
            ->orderBy('position')
            ->get());
    }

    public function certifications(): JsonResponse
    {
        return $this->list(Certification::query()
            ->where('is_visible', true)
            ->with(['translations', 'technologies' => fn ($query) => $query->where('is_visible', true)])
            ->orderByDesc('issued_on')
            ->orderBy('position')
            ->get());
    }

    public function achievements(): JsonResponse
    {
        return $this->list(Achievement::query()
            ->where('is_visible', true)
            ->with('translations')
            ->orderByDesc('achieved_on')
            ->orderBy('position')
            ->get());
    }

    /**
     * Chronologie : un événement lié à une source (expérience, projet…) n'apparaît que si sa source
     * est elle-même publique ; ses traductions ne servent alors que de surcharge.
     */
    public function timeline(): JsonResponse
    {
        $events = TimelineEvent::query()
            ->where('is_visible', true)
            ->with(['translations', 'eventable.translations'])
            ->orderByDesc('event_date')
            ->orderBy('position')
            ->get()
            ->filter(fn (TimelineEvent $event): bool => $event->eventable_type === null || PublicVisibility::allows($event->eventable));

        return $this->list($events);
    }

    /**
     * Témoignages approuvés, avec le consentement de leur auteur, mis en avant d'abord.
     */
    public function testimonials(): JsonResponse
    {
        return $this->list(Testimonial::query()
            ->where('status', TestimonialStatus::Approved->value)
            ->where('consent_given', true)
            ->where('is_visible', true)
            ->with(['translations', 'project' => fn ($query) => $query->published()->with('translations')])
            ->orderByDesc('is_featured')
            ->orderBy('position')
            ->orderByDesc('given_on')
            ->get());
    }
}
