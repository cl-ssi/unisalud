<?php

use App\Http\Controllers\ClaveUnicaController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/** 
 * Rutas para login y logout con clave única y local
 **/
// env('APP_URL').'/auth/claveunica/redirect'
// env('APP_URL').'/auth/claveunica/callback'   // ClaveÚnica: redirect_uri
// env('APP_URL').'/logout/claveunica/redirect' // ClaveÚnica: logout_uri
// env('APP_URL').'/logout/claveunica/callback'
// env('APP_URL').'/logout/local'
Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'authRedirect'])->name('socialite.auth.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'authCallback'])->name('socialite.auth.callback');
Route::get('/logout/{provider}/redirect', [SocialiteController::class, 'logoutRedirect'])->name('socialite.logout.redirect');
Route::get('/logout/{provider}/callback', [SocialiteController::class, 'logoutCallback']);
Route::get('/logout/local', [SocialiteController::class, 'logoutLocal'])->name('socialite.logout.local');


/**
 * Estas rutas son legacy para el siremex, cuando el siremex desaparezca ya no se usa
 * igual que el ClaveUnicaController
 */
Route::get('/claveunica/callback', [ClaveUnicaController::class, 'callback'])->name('claveunica.callback');
// Route::get('/claveunica/logout', [ClaveUnicaController::class, 'logout'])->name('claveunica.logout');
Route::get('/claveunica/{route?}', [ClaveUnicaController::class, 'autenticar'])->name('claveunica.login');