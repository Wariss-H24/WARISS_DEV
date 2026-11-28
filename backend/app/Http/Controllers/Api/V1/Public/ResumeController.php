<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\AnalyticsEventName;
use App\Enums\ResumeTarget;
use App\Http\Controllers\Controller;
use App\Models\Resume\Resume;
use App\Services\Analytics\AudienceTracker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CV téléchargeables (D15) : seule la version courante d'un CV public et actif est servie.
 * Le fichier reste sur le disque privé ; il n'est accessible que par ce téléchargement, compté
 * dans la mesure d'audience (événement cv_download).
 */
class ResumeController extends Controller
{
    public function index(): JsonResponse
    {
        $resumes = Resume::query()
            ->downloadable()
            ->with('currentVersion.media')
            ->orderByDesc('is_default')
            ->orderBy('locale')
            ->get();

        return response()->json(['data' => $resumes->map(fn (Resume $resume): array => [
            'title' => $resume->title,
            'target' => $resume->target->value,
            'locale' => $resume->locale,
            'is_default' => $resume->is_default,
            'version_number' => $resume->currentVersion?->version_number,
            'size_bytes' => $resume->currentVersion?->media->size_bytes,
            'updated_at' => $resume->currentVersion?->created_at?->toIso8601String(),
        ])->values()]);
    }

    /**
     * CV de la langue demandée (cible facultative) ; à défaut, celui de la langue par défaut.
     */
    public function download(Request $request, AudienceTracker $tracker): StreamedResponse
    {
        $validated = $request->validate(['target' => ['sometimes', Rule::enum(ResumeTarget::class)]]);
        $locales = array_unique([app()->getLocale(), (string) config('translatable.fallback_locale')]);

        $resume = null;

        foreach ($locales as $locale) {
            $resume = Resume::query()
                ->downloadable()
                ->where('locale', $locale)
                ->when(isset($validated['target']), fn (Builder $query) => $query->where('target', $validated['target']))
                ->with('currentVersion.media')
                ->orderByDesc('is_default')
                ->latest('updated_at')
                ->first();

            if ($resume !== null) {
                break;
            }
        }

        $media = $resume?->currentVersion?->media;
        abort_if($media === null || $media->isExternal() || ! Storage::disk((string) $media->disk)->exists((string) $media->path), Response::HTTP_NOT_FOUND);

        $tracker->recordEvent($request, AnalyticsEventName::CvDownload, "/{$resume->locale}/resume");

        return Storage::disk((string) $media->disk)->download((string) $media->path, 'CV-'.Str::slug($resume->title).'.pdf', [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
