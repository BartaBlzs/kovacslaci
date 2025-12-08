<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CountyController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\PostalCodeController;

// Auth endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public endpoints
Route::get('/counties', [CountyController::class, 'index']);
Route::get('/counties/{county}', [CountyController::class, 'show']);
Route::get('/counties/{county}/stats', [CountyController::class, 'stats']);

Route::get('/cities', [CityController::class, 'index']);
Route::get('/cities/starting-letters', [CityController::class, 'getStartingLetters']);
Route::get('/cities/{city}', [CityController::class, 'show']);
// ÚJ: ABC szűrő végpontok
Route::get('/cities/initial-letters/{countyId}', [CityController::class, 'getInitialLetters']);
Route::get('/cities/filter/{countyId}/{letter}', [CityController::class, 'filterByCountyAndLetter']);

Route::get('/postal-codes', [PostalCodeController::class, 'index']);
Route::get('/postal-codes/{postalCode}', [PostalCodeController::class, 'show']);

// Protected endpoints (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Counties
    Route::post('/counties', [CountyController::class, 'store']);
    Route::put('/counties/{county}', [CountyController::class, 'update']);
    Route::delete('/counties/{county}', [CountyController::class, 'destroy']);
    
    // Cities
    Route::post('/cities', [CityController::class, 'store']);
    Route::put('/cities/{city}', [CityController::class, 'update']);
    Route::delete('/cities/{city}', [CityController::class, 'destroy']);
    
    // Postal Codes
    Route::post('/postal-codes', [PostalCodeController::class, 'store']);
    Route::put('/postal-codes/{postalCode}', [PostalCodeController::class, 'update']);
    Route::delete('/postal-codes/{postalCode}', [PostalCodeController::class, 'destroy']);
});