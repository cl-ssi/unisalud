<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Filament\Widgets;

use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\Authenticate;

use Filament\PanelProvider;
use Filament\Panel;
use Filament\Pages;

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
use Awcodes\FilamentGravatar\GravatarProvider;
use Awcodes\FilamentGravatar\GravatarPlugin;
use Hasnayeen\Themes\ThemesPlugin;
use \Hasnayeen\Themes\Themes;

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
                fn() => view('auth.socialite.claveunica')
            )

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->sidebarFullyCollapsibleOnDesktop()
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
                ThemesPlugin::make(),
                GravatarPlugin::make(),
                EnvironmentIndicatorPlugin::make()
                    ->visible(fn() => auth()->user()?->can('be god')),
            ]);
    }
}
