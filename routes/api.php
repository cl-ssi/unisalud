<?php


use App\Http\Controllers\Api\GeoPaddsController;
use Illuminate\Support\Facades\Route;

Route::get('/geopadds', [GeoPaddsController::class, 'markers']);
Route::get('/test', function () {
    return response()->json(['message' => 'Test route']);
});
