<?php

namespace App\Providers;

use App\Models\Blog\Post;
use App\Models\Career\Achievement;
use App\Models\Career\Certification;
use App\Models\Career\Education;
use App\Models\Career\Experience;
use App\Models\Catalog\Skill;
use App\Models\Lab\Experiment;
use App\Models\Media\Media;
use App\Models\Pages\Page;
use App\Models\Portfolio\CaseStudy;
use App\Models\Portfolio\Project;
use App\Models\Services\Service;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerLocalDevelopmentTools();
    }

    /**
     * Register Telescope only in the local environment, where its dev dependency is installed.
     */
    protected function registerLocalDevelopmentTools(): void
    {
        if (! $this->app->environment('local') || ! class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            return;
        }

        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePasswordPolicy();
        $this->configureMorphMap();
        $this->configureRateLimiting();
    }

    /**
     * Limites de requêtes par adresse IP (protection contre les abus et le spam).
     *
     * public-read : lecture des contenus ; public-forms : contact et newsletter ;
     * tracking : mesure d'audience ; auth : tentatives de connexion (en plus de la limite
     * par adresse e-mail appliquée dans LoginRequest).
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('public-read', fn (Request $request): Limit => Limit::perMinute(120)->by((string) $request->ip()));
        RateLimiter::for('public-forms', fn (Request $request): Limit => Limit::perMinute(5)->by((string) $request->ip()));
        RateLimiter::for('tracking', fn (Request $request): Limit => Limit::perMinute(60)->by((string) $request->ip()));
        RateLimiter::for('auth', fn (Request $request): Limit => Limit::perMinute(20)->by((string) $request->ip()));
    }

    /**
     * Alias courts des types polymorphes (mediables, seo_metadata, activity_logs…).
     *
     * Les tables stockent l'alias, jamais le nom de la classe : renommer une classe
     * ne casse pas les données. Un modèle absent de cette liste provoque une erreur.
     * Chaque nouveau modèle rattachable est ajouté ici avec son module.
     */
    protected function configureMorphMap(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'media' => Media::class,
            'skill' => Skill::class,
            'page' => Page::class,
            'project' => Project::class,
            'case_study' => CaseStudy::class,
            'service' => Service::class,
            'experiment' => Experiment::class,
            'post' => Post::class,
            'experience' => Experience::class,
            'education' => Education::class,
            'certification' => Certification::class,
            'achievement' => Achievement::class,
        ]);
    }

    /**
     * Politique de mot de passe unique pour toute l'application (Password::defaults()).
     *
     * En production, le mot de passe est aussi comparé aux fuites connues (Have I Been Pwned,
     * k-anonymat : seul un préfixe du hachage SHA-1 est envoyé).
     */
    protected function configurePasswordPolicy(): void
    {
        Password::defaults(function (): Password {
            $rule = Password::min(12)->letters()->mixedCase()->numbers()->symbols();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
