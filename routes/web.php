<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/api');
});

// Route to run migrations
Route::get('/migrate', function () {
    Artisan::call('migrate', ['--force' => true]);
    return "Migrations executed successfully.";
});

Route::get('/migrate-fresh', function () {
    Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
    return "Migrations executed successfully.";
});

// Route to clear configuration cache
Route::get('/config-clear', function () {
    Artisan::call('optimize:clear');
    return "Configuration cache cleared successfully.";
});
