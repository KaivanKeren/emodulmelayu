<?php

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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'postLogin'])->name('postLogin');

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('adminDashboard');
        Route::get('/search', [DashboardController::class, 'search'])->name('search');
        Route::get('/filter', [DashboardController::class, 'filter'])->name('users.filter');


        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::get('/accept/{id}', [UserController::class, 'accept'])->name('users.accept');
            Route::get('/{user}', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');

            Route::get('/filter', [UserController::class, 'filter'])->name('users.page.filter');
        });

        Route::prefix('assessments')->group(function () {
            Route::get('/', [AssessmentController::class, 'index'])->name('assessments.index');
            Route::get('/create', [AssessmentController::class, 'create'])->name('assessments.create');
            Route::get('/edit/{assessment}', [AssessmentController::class, 'edit'])->name('assessments.edit');
            Route::post('/', [AssessmentController::class, 'store'])->name('assessments.store');
            Route::get('/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');
            Route::put('/{assessment}', [AssessmentController::class, 'update'])->name('assessments.update');
            Route::delete('/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');
            Route::post('/{assessment}/regenerate-token', [AssessmentController::class, 'regenerateToken'])
                ->name('assessments.regenerate-token');
            Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
            Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
            Route::get('/questions/{question}', [QuestionController::class, 'edit'])->name('questions.edit');
            Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
            Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

            Route::get('/filter', [AssessmentController::class, 'filter'])->name('assessments.filter');
        });

        Route::prefix('materials')->group(function () {
            Route::get('/', [MaterialController::class, 'index'])->name('materials.index');
            Route::get('/create', [MaterialController::class, 'create'])->name('materials.create');
            Route::post('/', [MaterialController::class, 'store'])->name('materials.store');
            Route::get('/{material}', [MaterialController::class, 'show'])->name('materials.show');
            Route::get('/edit/{material}', [MaterialController::class, 'edit'])->name('materials.edit');
            Route::put('/{material}', [MaterialController::class, 'update'])->name('materials.update');
            Route::delete('/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
            Route::delete('/assets', [MaterialController::class, 'deleteAsset'])->name('materials.delete.asset');

            Route::get('/filter', [MaterialController::class, 'filter'])->name('materials.filter');
        });

        Route::prefix('discussions')->group(function () {
            Route::get('/', [DiscussionController::class, 'index'])->name('discussions.index');
            Route::get('/create', [DiscussionController::class, 'create'])->name('discussions.create');
            Route::post('/', [DiscussionController::class, 'store'])->name('discussions.store');
            Route::get('/{discussion}', [DiscussionController::class, 'show'])->name('discussions.show');
            Route::get('/edit/{discussion}', [DiscussionController::class, 'edit'])->name('discussions.edit');
            Route::put('/{discussion}', [DiscussionController::class, 'update'])->name('discussions.update');
            Route::delete('/{discussion}', [DiscussionController::class, 'destroy'])->name('discussions.destroy');

            Route::post('/{discussion}/messages', [MessageController::class, 'store'])->name('discussions.messages.store');
            Route::delete('/{discussion}/messages/{message}', [MessageController::class, 'destroy'])->name('discussions.messages.destroy');
        });

        Route::prefix('schools')->group(function () {
            Route::get('/', [SchoolController::class, 'index'])->name('schools.index');
            Route::get('/create', [SchoolController::class, 'create'])->name('schools.create');
            Route::post('/', [SchoolController::class, 'store'])->name('schools.store');
            Route::get('/{school}', [SchoolController::class, 'edit'])->name('schools.edit');
            Route::put('/{school}', [SchoolController::class, 'update'])->name('schools.update');
            Route::delete('/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');

            Route::get('/filter', [SchoolController::class, 'filter'])->name('schools.filter');
        });

        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
        Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
