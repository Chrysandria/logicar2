<?php

use App\Http\Controllers\AnomalieController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ExpeditionController;
use App\Http\Controllers\MouvementController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PlanningItemController;
use App\Http\Controllers\PlanningReportController;
use App\Http\Controllers\TrajetController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsureSnr;
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
    Route::put('/auth/password', [AuthController::class, 'updatePassword']);

    Route::middleware(EnsurePasswordChanged::class)->group(function () {
        Route::get('/emps', [EmployeController::class, 'index']);
        Route::get('/services', [EmployeController::class, 'services']);

        Route::get('/planning-history', [PlanningController::class, 'history']);
        Route::apiResource('/plannings', PlanningController::class)->except(['update', 'destroy']);
        Route::post('/plannings/{planning}/employees', [PlanningController::class, 'addEmployees'])
            ->name('plannings.employees.store');
        Route::get('/trigger', [PlanningItemController::class, 'find']);

        Route::middleware(EnsureSnr::class)->group(function () {
            Route::apiResource('/users', UserController::class)->only(['index', 'store', 'destroy']);
            Route::apiResource('/anomalies', AnomalieController::class)->only(['index', 'store']);
            Route::apiResource('/cars', CarController::class);
            Route::apiResource('/moves', MouvementController::class)->only(['index', 'store', 'update']);
            Route::get('/expeditions/available-cars', [ExpeditionController::class, 'availableCars']);
            Route::apiResource('/expeditions', ExpeditionController::class)->only(['store', 'update']);
            Route::delete('/expeditions/{expedition}/planning-items/{planningItem}', [ExpeditionController::class, 'removePlanningItem'])
                ->name('expeditions.planning-items.destroy');
            Route::get('/plannings/{planning}/report', PlanningReportController::class)->name('plannings.report');
            Route::put('/planning-items/{planningItem}/expedition', [PlanningItemController::class, 'updateExpedition'])
                ->name('planning-items.expedition.update');
            Route::apiResource('/trajets', TrajetController::class);
        });
    });
});

Route::get('/{any}', function () {
    return view('index');
})->where('any', '.*');
