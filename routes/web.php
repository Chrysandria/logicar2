<?php

use App\Http\Controllers\AnomalieController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\MouvementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('/auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::middleware('auth')->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'user');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/emps/{service}', [EmployeController::class, 'index']);
    Route::get('/services', [EmployeController::class, 'services']);

    Route::apiResource('anomalies', AnomalieController::class)->only(['index', 'store']);
    Route::apiResource('cars', CarController::class);
    Route::apiResource("/moov",MouvementController::class)->only(["index","store","update"]);
});
