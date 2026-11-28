<?php

namespace App\Http\Controllers\Api\V1\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsReport;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Statistiques d'audience (D17) : visites, visiteurs du jour, partages, provenances, appareils,
 * pays, contenus les plus vus et les plus partagés. Données anonymes (D-16) : visibles par les
 * deux comptes.
 */
class AnalyticsController extends Controller
{
    public function __invoke(Request $request, AnalyticsReport $report): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $to = isset($validated['to']) ? CarbonImmutable::parse($validated['to']) : CarbonImmutable::today();
        $from = isset($validated['from']) ? CarbonImmutable::parse($validated['from']) : $to->subDays(29);

        if ($from->diffInDays($to) > 366) {
            return response()->json([
                'message' => __('La période ne peut pas dépasser 367 jours.'),
                'errors' => ['from' => [__('La période ne peut pas dépasser 367 jours.')]],
            ], 422);
        }

        return response()->json(['data' => $report->build($from, $to, (int) ($validated['limit'] ?? 10))]);
    }
}
