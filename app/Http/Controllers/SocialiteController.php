<?php

namespace App\Http\Controllers;

use App\Models\Identifier;
use Filament\Facades\Filament;
use Http;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function authRedirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function authCallback(string $provider)
    {
        /**
         * Login legacy solo para SIREMX, eliminar cuando ya no se use
         * Eliminar el ClaveUnicaController y las entradas de ese controlador en el web.php
         */
        if( request()->input('route') ) {
            $response = Http::asForm()->post("https://accounts.claveunica.gob.cl/openid/token/", [
                'client_id'     => env("CLAVEUNICA_CLIENT_ID"),
                'client_secret' => env("CLAVEUNICA_CLIENT_SECRET"),
                'redirect_uri'  => env("CLAVEUNICA_REDIRECT_URI"),
                'grant_type'    => 'authorization_code',
                'code'          => request()->input('code'),
                'state'         => request()->input('state'),
            ]);

            $responseData = json_decode($response->body());

            if( $responseData->access_token AND isset($route) ) {
                $redirect = str_ireplace('uni.', $route . '.', parse_url(env('APP_URL'), PHP_URL_HOST));
                return redirect("https://$redirect/claveunica/login/{$responseData->access_token}");
            }
            elseif( $responseData->error ) {
                return redirect()->route('filament.admin.auth.login')
                    ->withErrors(['msg' => $responseData->error_description]);
            }
        }


        try {
            $response = Socialite::driver($provider)->user();
            $user = Identifier::firstWhere(['value' => $response->getId()])?->user;

            if ( $user ) {
                auth()->login($user);
                return redirect()->intended(route('filament.admin.pages.dashboard'));
            } else {
                session(['userNotFound' => true ]);
                return redirect()->route('socialite.logout.redirect', ['provider' => $provider]);
            }
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['msg' => 'State inválido: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['msg' => 'Excepción general:: ' . $e->getMessage()]);
        }
    }

    public function logoutRedirect(string $provider)
    {
        # logout_uri = https://xxx.saludtarapaca.gob.cl/logout/claveunica/callback
        $logout_uri = env('APP_URL').'/logout/'.$provider.'/callback';
        return redirect()->away('https://accounts.claveunica.gob.cl/api/v1/accounts/app/logout?redirect='.$logout_uri);
    }
    
    public function logoutCallback(string $provider)
    {
        return redirect()->route('socialite.logout.local');
    }

    public function logoutLocal()
    {
        // Comprueba si existe la variable de sesión 'userNotFound'
        $userNotFound = session()->has('userNotFound');

        // Cerrar la sesión local y renovar los tokens
        Filament::auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        // Si la variable de sesión 'userNotFound' existe, la vuelve a guardar para mostrar en pantalla
        return redirect()->route('filament.admin.auth.login')
            ->withErrors($userNotFound ? ['msg' => 'El usuario no existe'] : []);
    }

}