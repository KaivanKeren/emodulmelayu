<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(middleware: 'auth:sanctum');

// API routes group with proper middleware
Route::middleware('api')->group(function () {
    Route::post('/user/login', [AuthController::class, 'apiLogin'])->name('apiLogin');
    Route::post('/user/register', [AuthController::class, 'apiRegister'])->name('apiRegister');

    Route::get('/schools', [SchoolController::class, 'indexApi']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/user/logout', [AuthController::class, 'apiLogout'])->name('apiLogout')->middleware('auth:sanctum');
        Route::get('/search', [DashboardController::class, 'search']);


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
            Route::post('/', [SchoolController::class, 'storeApi']);
            Route::get('/{school}', [SchoolController::class, 'showApi']);
            Route::put('/{school}', [SchoolController::class, 'updateApi']);
            Route::delete('/{school}', [SchoolController::class, 'destroyApi']);
        });

        Route::prefix('materials')->group(function () {
            Route::get('/', [MaterialController::class, 'apiIndex']);
            Route::post('/', [MaterialController::class, 'apiStore']);
            Route::get('/{material}', [MaterialController::class, 'apiShow']);
            // Route::put('/{material}', [MaterialController::class, 'apiUpdate']);
            Route::delete('/{material}', [MaterialController::class, 'apiDestroy']);
        });

        // Route::prefix('models')->group(function () {
        //     Route::get('/', [ModelARController::class, 'apiIndex']);
        //     Route::post('/', [ModelARController::class, 'apiStore']);
        //     Route::get('/{model}', [ModelARController::class, 'apiShow']);
        //     // Route::put('/{model}', [ModelARController::class, 'apiUpdate']);
        //     Route::delete('/{model}', [ModelARController::class, 'apiDestroy']);
        // });

        Route::prefix('discussions')->group(function () {
            Route::get('/', [DiscussionController::class, 'apiIndex']);
            Route::post('/', [DiscussionController::class, 'apiStore']);
            Route::get('/{discussion}', [DiscussionController::class, 'apiShow']);
            Route::put('/{discussion}', [DiscussionController::class, 'apiUpdate']);
            Route::delete('/{discussion}', [DiscussionController::class, 'apiDestroy']);

            Route::get('/{discussion}/messages', [MessageController::class, 'index']);
            Route::post('/{discussion}/messages', [MessageController::class, 'store']);
            Route::delete('/{discussion}/messages/{id}', [MessageController::class, 'destroy']);
        });

        Route::get('/calendar', [CalendarController::class, 'apiIndex']);
        Route::post('/events', [EventController::class, 'store']);
        Route::get('/events/{id}', [EventController::class, 'show']);
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']);
    });
});
