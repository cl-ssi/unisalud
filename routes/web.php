<?php

use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rutas para login y logout con clave única y local
Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'authRedirect'])->name('socialite.auth.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'authCallback'])->name('socialite.auth.callback');
Route::get('/logout/{provider}/redirect', [SocialiteController::class, 'logoutRedirect'])->name('socialite.logout.redirect');
Route::get('/logout/{provider}/callback', [SocialiteController::class, 'logoutCallback']);
Route::get('/logout/local', [SocialiteController::class, 'logoutLocal'])->name('socialite.logout.local');
