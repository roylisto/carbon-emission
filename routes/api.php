<?php

use App\Http\Controllers\Api\FlightRouteController;
use App\Http\Controllers\Api\TrainRouteController;
use App\Http\Controllers\Api\HotelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/flight', [FlightRouteController::class, 'calculate']);
Route::post('/train', [TrainRouteController::class, 'calculate']);
Route::post('/hotel', [HotelController::class, 'calculate']);
