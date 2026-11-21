<?php

namespace App\Providers;

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
