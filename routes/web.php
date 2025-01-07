<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboarController;
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
    Route::get('/siswa/dashboard', [DashboarController::class, 'studentDashboard'])->name('studentDashboard');
    Route::get('/guru/dashboard', [DashboarController::class, 'teacherDashboard'])->name('teacherDashboard');

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboarController::class, 'adminDashboard'])->name('adminDashboard');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/accept/{id}', [UserController::class, 'accept'])->name('users.accept');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');

        Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
    });
    
    // Route::resource('assessments', AssessmentController::class);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
