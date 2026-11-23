<?php

use App\Http\Controllers\Api\V1\Admin\Blog\PostCategoryController;
use App\Http\Controllers\Api\V1\Admin\Blog\PostController;
use App\Http\Controllers\Api\V1\Admin\Blog\TagController;
use App\Http\Controllers\Api\V1\Admin\Career\AchievementController;
use App\Http\Controllers\Api\V1\Admin\Career\CertificationController;
use App\Http\Controllers\Api\V1\Admin\Career\EducationController;
use App\Http\Controllers\Api\V1\Admin\Career\ExperienceController;
use App\Http\Controllers\Api\V1\Admin\Career\TimelineEventController;
use App\Http\Controllers\Api\V1\Admin\Catalog\SkillCategoryController;
use App\Http\Controllers\Api\V1\Admin\Catalog\SkillController;
use App\Http\Controllers\Api\V1\Admin\Catalog\TechnologyCategoryController;
use App\Http\Controllers\Api\V1\Admin\Catalog\TechnologyController;
use App\Http\Controllers\Api\V1\Admin\Communication\NewsletterCampaignController;
use App\Http\Controllers\Api\V1\Admin\Communication\TestimonialController;
use App\Http\Controllers\Api\V1\Admin\Identity\SocialLinkController;
use App\Http\Controllers\Api\V1\Admin\Lab\ExperimentCategoryController;
use App\Http\Controllers\Api\V1\Admin\Lab\ExperimentController;
use App\Http\Controllers\Api\V1\Admin\Media\MediaCollectionController;
use App\Http\Controllers\Api\V1\Admin\Pages\PageController;
use App\Http\Controllers\Api\V1\Admin\Pages\PageSectionController;
use App\Http\Controllers\Api\V1\Admin\Portfolio\CaseStudyController;
use App\Http\Controllers\Api\V1\Admin\Portfolio\ProjectCategoryController;
use App\Http\Controllers\Api\V1\Admin\Portfolio\ProjectController;
use App\Http\Controllers\Api\V1\Admin\Resume\ResumeController;
use App\Http\Controllers\Api\V1\Admin\Seo\RedirectController;
use App\Http\Controllers\Api\V1\Admin\Services\ServiceCategoryController;
use App\Http\Controllers\Api\V1\Admin\Services\ServiceController;
use App\Http\Controllers\Api\V1\Admin\Settings\NavigationItemController;
use App\Http\Controllers\Api\V1\Admin\Settings\NavigationMenuController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API d'administration (/api/v1/admin)
|--------------------------------------------------------------------------
|
| Session Sanctum obligatoire ; le compte viewer est en lecture seule (EnsureCanWrite) et ne voit
| pas les données sensibles (gate view-sensitive-data).
|
| Ressources CRUD : GET liste, GET détail, POST création, PATCH/PUT modification, DELETE suppression.
| Contenus avec corbeille : POST {id}/restore et DELETE {id}/force en plus.
|
*/

$crud = static function (string $uri, string $controller, bool $softDeletes = false): void {
    Route::apiResource($uri, $controller)->parameters([$uri => 'id'])->whereNumber('id');

    if ($softDeletes) {
        Route::post("{$uri}/{id}/restore", [$controller, 'restore'])->whereNumber('id')->name("{$uri}.restore");
        Route::delete("{$uri}/{id}/force", [$controller, 'forceDestroy'])->whereNumber('id')->name("{$uri}.force-destroy");
    }
};

// Catalogue (D4, D5)
$crud('technology-categories', TechnologyCategoryController::class);
$crud('technologies', TechnologyController::class);
$crud('skill-categories', SkillCategoryController::class);
$crud('skills', SkillController::class);

// Portfolio (D6, D7)
$crud('project-categories', ProjectCategoryController::class);
$crud('projects', ProjectController::class, softDeletes: true);
$crud('case-studies', CaseStudyController::class, softDeletes: true);

// Services (D3)
$crud('service-categories', ServiceCategoryController::class);
$crud('services', ServiceController::class, softDeletes: true);

// Laboratoire (D11)
$crud('experiment-categories', ExperimentCategoryController::class);
$crud('experiments', ExperimentController::class, softDeletes: true);

// Blog (D12)
$crud('post-categories', PostCategoryController::class);
$crud('tags', TagController::class);
$crud('posts', PostController::class, softDeletes: true);

// Parcours (D8, D9, D10)
$crud('experiences', ExperienceController::class, softDeletes: true);
$crud('educations', EducationController::class);
$crud('certifications', CertificationController::class);
$crud('achievements', AchievementController::class);
$crud('timeline-events', TimelineEventController::class);

// Témoignages et newsletter (D13)
$crud('testimonials', TestimonialController::class);
$crud('newsletter-campaigns', NewsletterCampaignController::class);

// Pages, navigation, profil (D1, D2, D18)
$crud('pages', PageController::class, softDeletes: true);
$crud('page-sections', PageSectionController::class);
$crud('navigation-menus', NavigationMenuController::class);
$crud('navigation-items', NavigationItemController::class);
$crud('social-links', SocialLinkController::class);

// Médias, SEO, CV (D14, D15, D16)
$crud('media-collections', MediaCollectionController::class);
$crud('redirects', RedirectController::class);
$crud('resumes', ResumeController::class);
