<?php

use App\Http\Controllers\EmployeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get("/emps/{service}",[EmployeController::class,"index"]);
Route::get("/services",[EmployeController::class, "services"]);
