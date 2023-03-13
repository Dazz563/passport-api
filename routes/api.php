<?php

use App\Http\Controllers\AdminController;
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
Route::post('/upload_avatar', [AuthController::class, 'uploadRegisterAvatarImage']);

// (guarded)
Route::middleware('auth:api')->group(function () {
    // Auth Routes 
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Admin Routes
    Route::post('/create_user', [AdminController::class, 'registerUser'])->middleware('role:admin');
    Route::get('/get_all_users', [AdminController::class, 'getUsers'])->middleware('role:admin');
    Route::post('/edit_roles/{id}', [AdminController::class, 'updateUser'])->middleware('role:admin');
    Route::delete('/delete_user/{user}', [AdminController::class, 'deleteUser'])->middleware('role:admin');
    Route::put('/restore_user/{id}', [AdminController::class, 'restoreUser'])->middleware('role:admin');
    // Product Routes
    Route::resource('/products', ProductController::class)->middleware('role:admin|vendor');
});
