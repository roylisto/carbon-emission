<?php

use App\Http\Controllers\Api\FlightRouteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/flight', [FlightRouteController::class, 'calculate']);
