<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return 'Hello, world!';
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(middleware: 'auth:sanctum');

// API routes group with proper middleware
Route::middleware('api')->group(function () {
    Route::post('/user/login', [AuthController::class, 'apiLogin'])->name('apiLogin');
    Route::post('/user/register', [AuthController::class, 'apiRegister'])->name('apiRegister');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/user/logout', [AuthController::class, 'apiLogout'])->name('apiLogout')->middleware('auth:sanctum');
    });
});
