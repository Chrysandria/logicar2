<?php

use App\Http\Controllers\AnomalieController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ExpeditionController;
use App\Http\Controllers\MouvementController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PlanningItemController;
use App\Http\Controllers\TrajetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('login');

Route::prefix('/auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
    Route::get('/me', 'user');
});

Route::middleware('auth')->group(function () {
    Route::get('/emps', [EmployeController::class, 'index']);
    Route::get('/services', [EmployeController::class, 'services']);

    Route::apiResource('/anomalies', AnomalieController::class)->only(['index', 'store']);
    Route::apiResource('/cars', CarController::class);
    Route::apiResource('/moves', MouvementController::class)->only(['index', 'store', 'update']);
    Route::apiResource('/expeditions', ExpeditionController::class)->only(['store', 'update']);
    Route::apiResource('/plannings', PlanningController::class)->except(['update', 'destroy']);
    Route::apiResource('/trajets', TrajetController::class);
    Route::get('/trigger', [PlanningItemController::class, 'find']);
});

Route::get('/{any}', function () {
    return view('index');
})->where('any', '.*');
