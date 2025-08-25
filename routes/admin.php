<?php


use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\SeoSettingController;
use App\Http\Controllers\Api\Admin\AgentsController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\MaintenanceModeController;
use App\Http\Controllers\Api\Admin\PropertiesController;
use App\Http\Controllers\Api\Admin\UsersController;
use App\Http\Controllers\Api\CategoriesController;
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

    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'index']);
        Route::post('/', [UsersController::class, 'addUser']);
        Route::get('/stats', [UsersController::class, 'getStats']);
        Route::get('/{user}', [UsersController::class, 'show']);
        Route::put('/{user}/status', [UsersController::class, 'updateStatus']);
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


    Route::prefix('amenities')->group(function () {
        Route::post('/', [CategoriesController::class, 'createAmenity']);
        Route::get('/', [CategoriesController::class, 'getAmenities']);
    });


    Route::prefix('maintenance')->group(function () {
        Route::get('/', [MaintenanceModeController::class, 'index']);
        Route::put('/', [MaintenanceModeController::class, 'update']);
        Route::post('/toggle', [MaintenanceModeController::class, 'toggle']);
    });

    Route::post('/app-logo', [DashboardController::class, 'uploadAppLogo']);
    Route::post('/app-favicon', [DashboardController::class, 'uploadAppFavicon']);


    Route::get('seo/landing', [SeoSettingController::class, 'getLandingPageSEO']);
    Route::post('seo/landing', [SeoSettingController::class, 'saveLandingPageSEO']);
    Route::post('seo/landing/reset', [SeoSettingController::class, 'resetLandingPageSEO']);

    Route::apiResource('admins', AdminController::class);
    
    // Additional admin management routes
    Route::post('admins/{id}/resend-credentials', [AdminController::class, 'resendCredentials']);
    Route::patch('admins/{id}/toggle-status', [AdminController::class, 'toggleStatus']);
    Route::post('admins/bulk-action', [AdminController::class, 'bulkAction']);
    
 
});

