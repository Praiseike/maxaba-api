<?php

use App\Http\Controllers\Api\Agents\AgentsController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\NotificationsController;
use App\Http\Controllers\Api\PropertiesController;
use App\Http\Controllers\Api\ServerController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\RoommateController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [ServerController::class, 'index']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify', [AuthController::class, 'verifyToken']);

// Public property-related routes
Route::prefix('open')->group(function () {
    Route::get('roommates', [RoommateController::class, 'index']);

    Route::prefix('properties')->group(function () {
        Route::get('/search', [PropertiesController::class, 'searchProperties']);
        Route::get('/', [PropertiesController::class, 'getProperties']);
        Route::get('/i/{slug}', [PropertiesController::class, 'getPropertyBySlug']);
        Route::get('/{id}', [PropertiesController::class, 'getProperty']);
    });


    Route::get('/followers/{user}', [FollowController::class, 'followers']);
    Route::get('/following/{user}', [FollowController::class, 'following']);

    Route::get('/user/{user}', [ProfileController::class, 'getUser']);

});

Route::get('/categories', [CategoriesController::class, 'getCategories']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {


    // User Profile
    Route::prefix('user')->group(function () {
        Route::get('/profile', [ProfileController::class, 'index']);
        Route::post('/profile/save', [ProfileController::class, 'save']);
        Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
        Route::post('/profile/upload', [ProfileController::class, 'uploadProfilePic']);
        Route::post('/become-an-agent', [AgentsController::class, 'becomeAgent']);
        Route::get('/agent-application', [AgentsController::class, 'getAgentApplication']);
        Route::get('/{user}', [ProfileController::class, 'getUser']);

    });

    // Roommates
    Route::prefix('roommates')->group(function () {
        Route::post('/', [RoommateController::class, 'store']);
    });

    // Authenticated property actions
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertiesController::class, 'getProperties']);
        Route::get('/i/{slug}', [PropertiesController::class, 'getPropertyBySlug']);
        
        Route::post('/', [PropertiesController::class, 'store']);
        Route::get('/favourites', [PropertiesController::class, 'myFavourites']);
        Route::post('/{property}/favourite', [PropertiesController::class, 'favourite']);
        Route::delete('/{property}/favourite', [PropertiesController::class, 'unfavourite']);
        Route::post('{property}/mark-as-sold', [PropertiesController::class, 'markAsSold']);
        Route::post('{property}/mark-as-rented', [PropertiesController::class, 'marksAsRented']);
        Route::patch('/{id}/status', [PropertiesController::class, 'updatePropertyStatus']);
        Route::delete('/{id}', [PropertiesController::class, 'deleteProperty']);
        Route::put('/{id}', [PropertiesController::class, 'updateProperty']);
        Route::get('/{id}', [PropertiesController::class, 'getProperty']);
    });

    // Categories
    Route::prefix('categories')->group(function () {
        Route::post('/', [CategoriesController::class, 'addCategory']);
        Route::put('/{id}', [CategoriesController::class, 'updateCategory']);
        Route::delete('/{id}', [CategoriesController::class, 'deleteCategory']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationsController::class, 'index']);
        Route::get('/stats', [NotificationsController::class, 'getNotificationStats']);
    });
    Route::prefix('inbox')->group(function () {
        Route::get('/conversations', [ChatController::class, 'getConversations']);
        Route::post('/conversations', [ChatController::class, 'createConversation']);
        Route::get('/messages/{conversationId}', [ChatController::class, 'getMessages']);
        Route::post('/send-message', [ChatController::class, 'sendMessage']);
    });

    // Follow/Unfollow
    Route::post('/follow/{user}', [FollowController::class, 'follow']);
    Route::delete('/unfollow/{user}', [FollowController::class, 'unfollow']);
});


// New Google auth routes
Route::post('/auth/google/verify', [AuthController::class, 'verifyGoogleToken']);
Route::post('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']); // Optional