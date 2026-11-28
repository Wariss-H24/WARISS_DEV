<?php

use App\Http\Controllers\Api\V1\Public\CareerController;
use App\Http\Controllers\Api\V1\Public\CaseStudyController;
use App\Http\Controllers\Api\V1\Public\CatalogController;
use App\Http\Controllers\Api\V1\Public\ExperimentController;
use App\Http\Controllers\Api\V1\Public\PageController;
use App\Http\Controllers\Api\V1\Public\PostController;
use App\Http\Controllers\Api\V1\Public\PreviewController;
use App\Http\Controllers\Api\V1\Public\ProjectController;
use App\Http\Controllers\Api\V1\Public\RedirectController;
use App\Http\Controllers\Api\V1\Public\ResumeController;
use App\Http\Controllers\Api\V1\Public\SearchController;
use App\Http\Controllers\Api\V1\Public\ServiceController;
use App\Http\Controllers\Api\V1\Public\SiteController;
use App\Http\Controllers\Api\V1\Public\SitemapController;
use App\Http\Controllers\Api\V1\Public\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API publique (/api/v1/public)
|--------------------------------------------------------------------------
|
| Contenus publiés et visibles uniquement, dans la langue demandée (?locale=fr|en, sinon
| Accept-Language). Lecture libre, limitée en débit (public-read). Un contenu non publié répond
| 404, comme un contenu inexistant.
|
*/

$slug = '[a-z0-9]+(?:-[a-z0-9]+)*';

Route::get('site', SiteController::class)->name('site');
Route::get('pages/{page}', [PageController::class, 'show'])->where('page', $slug)->name('pages.show');

foreach ([
    'projects' => ProjectController::class,
    'case-studies' => CaseStudyController::class,
    'services' => ServiceController::class,
    'lab' => ExperimentController::class,
    'posts' => PostController::class,
] as $uri => $controller) {
    Route::get($uri, [$controller, 'index'])->name("{$uri}.index");
    Route::get("{$uri}/{slug}", [$controller, 'show'])->where('slug', $slug)->name("{$uri}.show");
}

Route::get('technologies', [CatalogController::class, 'technologies'])->name('technologies');
Route::get('skills', [CatalogController::class, 'skills'])->name('skills');
Route::get('taxonomies/{taxonomy}', [CatalogController::class, 'taxonomy'])->whereIn('taxonomy', array_keys(CatalogController::TAXONOMIES))->name('taxonomies');

Route::get('experiences', [CareerController::class, 'experiences'])->name('experiences');
Route::get('educations', [CareerController::class, 'educations'])->name('educations');
Route::get('certifications', [CareerController::class, 'certifications'])->name('certifications');
Route::get('achievements', [CareerController::class, 'achievements'])->name('achievements');
Route::get('timeline', [CareerController::class, 'timeline'])->name('timeline');
Route::get('testimonials', [CareerController::class, 'testimonials'])->name('testimonials');

Route::get('search', SearchController::class)->name('search');

Route::get('resume', [ResumeController::class, 'index'])->name('resume.index');
Route::get('resume/download', [ResumeController::class, 'download'])->name('resume.download');
Route::get('redirect', RedirectController::class)->name('redirect');
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('preview/{token}', PreviewController::class)
    ->where('token', '[a-z_]+\.[0-9]+\.[a-z]{2}\.[0-9]+\.[0-9a-f]{64}')
    ->name('preview');

// Mesure d'audience sans cookie (D-16)
Route::middleware('throttle:tracking')->prefix('track')->name('track.')->group(function (): void {
    Route::post('view', [TrackingController::class, 'view'])->name('view');
    Route::post('event', [TrackingController::class, 'event'])->name('event');
});
