<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboarController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'postLogin'])->name('postLogin');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'postRegister'])->name('postRegister');

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboarController::class, 'adminDashboard'])->name('adminDashboard');


        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::get('/accept/{id}', [UserController::class, 'accept'])->name('users.accept');
            Route::get('/{user}', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::prefix('assessments')->group(function () {
            Route::get('/', [AssessmentController::class, 'index'])->name('assessments.index');
            Route::get('/create', [AssessmentController::class, 'create'])->name('assessments.create');
            Route::get('/edit/{assessment}', [AssessmentController::class, 'edit'])->name('assessments.edit');
            Route::post('/', [AssessmentController::class, 'store'])->name('assessments.store');
            Route::get('/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');
            Route::put('/{assessment}', [AssessmentController::class, 'update'])->name('assessments.update');
            Route::delete('/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');
            Route::get('/questions/create', [QuestionController::class, 'create'])->name('questions.create');
            Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
        });
        
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
        Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
