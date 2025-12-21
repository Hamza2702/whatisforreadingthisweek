<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SessionController;

Route::get('/', function () {
    return view('Site/index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');

// Register
Route::middleware(['auth', 'isAdmin'])->group(function () {
    Route::get('/register', [UserController::class, 'create']);
    Route::post('/register', [UserController::class, 'store'])->name('register');
});

Route::patch('/user', [UserController::class, 'update'])->middleware('auth');


// Login
Route::get('/login', [SessionController::class, 'show'])->name('login')->middleware('guest');
Route::post('/login', [SessionController::class, 'create'])->middleware('guest');
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth');

// Forgot Password
Route::get('/forgot-password', function () {
    return view('auth/forgot-password');
});
Route::post('forgot-password', [UserController::class, 'forgotPassword']);
