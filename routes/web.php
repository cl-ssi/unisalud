<?php

use App\Http\Controllers\ClaveUnicaController;
use App\Http\Controllers\SocialiteController;
use App\Jobs\TestQueueJob;
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
 * Esta ruta es legacy para el SIREMX, eliminar cuando ya no se ocupe
 * igual que el ClaveUnicaController y el fragmento de codigo dentro del SocialiteController
 */
Route::get('/claveunica/{route?}', [ClaveUnicaController::class, 'autenticar'])->name('claveunica.login');


Route::get('/test/queue', function () {
    // Despachar un trabajo a la cola
    Queue::push(new TestQueueJob());
    return 'Trabajo de cola despachado correctamente.';
});