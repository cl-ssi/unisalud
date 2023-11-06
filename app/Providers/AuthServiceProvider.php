<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
// use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //TODO: esto se está ocupando? en laravel 9 da error routes()
        //Rutas para oauth2 passport test
        // if (! $this->app->routesAreCached()) {
        //     Passport::routes();
        // }
        // Passport::tokensExpireIn(now()->addMinutes(30));
    }
}
