<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FlightRouteController;
use App\Http\Controllers\Api\TrainRouteController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\API\AuthController;

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/flight', [FlightRouteController::class, 'calculate']);
    Route::post('/train', [TrainRouteController::class, 'calculate']);
    Route::post('/hotel', [HotelController::class, 'calculate']);
});
