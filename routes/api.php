<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Auth Routes (unguarded)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot_password', [AuthController::class, 'forgotPassword']);
Route::post('/reset_password_with_token', [AuthController::class, 'resetPasswordWithToken']);

Route::middleware('auth:api')->group(function () {
    // Auth Routes (guarded)
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Product Routes (guarded)
    Route::resource('/products', ProductController::class)->middleware('role:admin|user');
});
