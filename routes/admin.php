<?php

use App\Http\Controllers\Api\Admin\AgentsController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\PropertiesController;
use Illuminate\Support\Facades\Route;

Route::post('auth/verify', [AuthController::class, 'verifyCode']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'getStats']);
    }); 


    Route::prefix('agents')->group(function () {
        Route::get('/', [AgentsController::class, 'index']);
        Route::post('/', [AgentsController::class, 'addAgent']);
        Route::get('/stats', [AgentsController::class, 'getStats']);
        Route::get('/{agent}', [AgentsController::class, 'show']);
        Route::put('/{agent}/status', [AgentsController::class, 'updateStatus']);
    });

    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertiesController::class, 'getProperties']);
        Route::post('/', [PropertiesController::class, 'store']);
        Route::get('/search', [PropertiesController::class, 'searchProperties']);
        Route::get('/stats', [PropertiesController::class, 'getStats']);
        Route::put('/{id}/status', [PropertiesController::class, 'updatePropertyStatus']);
        Route::get('/{id}', [PropertiesController::class, 'getProperty']);
        Route::delete('/{id}', [PropertiesController::class, 'deleteProperty']);
    });

    Route::post('/app-logo', [DashboardController::class, 'uploadAppLogo']);
    Route::post('/app-favicon', [DashboardController::class, 'uploadAppFavicon']);
});

