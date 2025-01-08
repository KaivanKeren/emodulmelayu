<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UserController;
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

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'apiIndex'])->name('apiIndex');
            Route::get('/{user}', [UserController::class, 'apiShow'])->name('apiShow');
            Route::post('/', [UserController::class, 'apiStore'])->name('apiStore');
            Route::put('/{user}', [UserController::class, 'apiUpdate'])->name('apiUpdate');
            Route::delete('/{user}', [UserController::class, 'apiDestroy'])->name('apiDestroy');
        });

        Route::prefix('assessments')->group(function () {
            Route::get('/', [AssessmentController::class, 'apiIndex']);
            Route::post('/', [AssessmentController::class, 'apiStore']);
            Route::get('/{assessment}', [AssessmentController::class, 'apiShow']);
            Route::put('/{assessment}', [AssessmentController::class, 'apiUpdate']);
            Route::delete('/{assessment}', [AssessmentController::class, 'apiDestroy']);

            Route::get('{assessment}/questions/', [QuestionController::class, 'apiIndex']);
            Route::post('{assessment}/questions/', [QuestionController::class, 'apiStore']);
            Route::get('{assessment}/questions/{question}', [QuestionController::class, 'apiShow']);
            Route::put('{assessment}/questions/{question}', [QuestionController::class, 'apiUpdate']);
            Route::delete('{assessment}/questions/{question}', [QuestionController::class, 'apiDestroy']);
        });
    });
});
