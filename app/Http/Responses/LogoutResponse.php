<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse
    {
        if ( env('APP_ENV') == 'local' ) {
            return redirect()->route('socialite.logout.local');
        } else {
            return redirect()->route('socialite.logout.redirect', ['provider' => 'claveunica']);
        }
    }
}