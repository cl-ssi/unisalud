<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use Illuminate\Support\Facades\Event;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** Para que los Policies tengan la misma estructura de los modelos */
        Gate::guessPolicyNamesUsing(function ($modelClass) {
            // Reemplaza 'App\Models' por 'App\Policies' y añade 'Policy' al final del nombre de la clase
            $policyClass = str_replace('App\Models', 'App\Policies', $modelClass) . 'Policy';
    
            return $policyClass;
        });

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('claveunica', \SocialiteProviders\ClaveUnica\Provider::class);
        });
    }
}
