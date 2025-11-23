<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CountyController;
use App\Http\Controllers\Web\CityController;
use App\Http\Controllers\Web\DashboardController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Counties
    Route::resource('counties', CountyController::class);
    Route::get('/counties/export/csv', [CountyController::class, 'exportCsv'])->name('counties.export.csv');
    Route::get('/counties/export/pdf', [CountyController::class, 'exportPdf'])->name('counties.export.pdf');
    
    // Cities
    Route::resource('cities', CityController::class);
    Route::get('/cities/export/csv', [CityController::class, 'exportCsv'])->name('cities.export.csv');
    Route::get('/cities/export/pdf', [CityController::class, 'exportPdf'])->name('cities.export.pdf');
});