<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Widgets;
use Awcodes\FilamentGravatar\GravatarPlugin;
use Awcodes\FilamentGravatar\GravatarProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;

use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
// use Filament\Widgets;


use Filament\Support\Colors\Color;
use Filament\Navigation\NavigationGroup;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->renderHook(
                'panels::auth.login.form.after',
                fn () => view('auth.socialite.claveunica')
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Usuarios'),
                NavigationGroup::make()
                    ->label('Exámenes Mamarios')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Parámetros')
                    ->collapsed(),
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\LogoServicioWidget::class,
                Widgets\WelcomeWidget::class,
                // Widgets\Condition\DependentUserMapWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->defaultAvatarProvider(GravatarProvider::class)
            ->plugins([
                GravatarPlugin::make(),
                EnvironmentIndicatorPlugin::make()
                    ->visible(fn () => auth()->user()?->can('be god'))
            ]);
    }
}
