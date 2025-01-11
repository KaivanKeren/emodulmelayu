<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SchoolController;
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

            Route::get('{assessment}/results', [AssessmentController::class, 'apiGetAllResults']);

            Route::get('{assessment}/questions/', [QuestionController::class, 'apiIndex']);
            Route::post('{assessment}/questions/', [QuestionController::class, 'apiStore']);
            Route::get('{assessment}/questions/{question}', [QuestionController::class, 'apiShow']);
            Route::put('{assessment}/questions/{question}', [QuestionController::class, 'apiUpdate']);
            Route::delete('{assessment}/questions/{question}', [QuestionController::class, 'apiDestroy']);

            Route::post('{assessment}/questions/{question}/answers', [AnswerController::class, 'apiStore']);
            Route::get('{assessment}/questions/{question}/answers', [AnswerController::class, 'apiIndex']);
            Route::get('{assessment}/questions/{question}/answers/{answer}', [AnswerController::class, 'apiShow']);
            Route::put('{assessment}/questions/{question}/answers', [AnswerController::class, 'apiUpdate']);
            Route::delete('{assessment}/questions/{question}/answers', [AnswerController::class, 'apiDestroy']);
            Route::get('{assessment}/questions/{question}/results', [AnswerController::class, 'apiGetResults']);
        });

        Route::prefix('schools')->group(function () {
            Route::get('/', [SchoolController::class, 'indexApi']);
            Route::post('/', [SchoolController::class, 'storeApi']);
            Route::get('/{school}', [SchoolController::class, 'showApi']);
            Route::put('/{school}', [SchoolController::class, 'updateApi']);
            Route::delete('/{school}', [SchoolController::class, 'destroyApi']);
        });

        Route::get('/calendar', [CalendarController::class, 'apiIndex']);
        Route::post('/events', [EventController::class, 'store']);
        Route::get('/events/{id}', [EventController::class, 'show']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);
    });
});
