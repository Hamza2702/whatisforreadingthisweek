<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SessionController;

Route::get('/', function () {
    return view('index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

// Register
Route::get('/register',[UserController::class,'create'])->middleware('guest');
Route::post('/register',[UserController::class,'store'])->middleware('guest');
Route::patch('/user',[UserController::class,'update'])->middleware('auth');
Route::post('/register', [UserController::class, 'store'])->name('register');

// Login
Route::get('/login', [SessionController::class, 'show'])->name('login')->middleware('guest');
Route::post('/login', [SessionController::class, 'create'])->middleware('guest');
Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth');

// Forgot Password
Route::get('/forgot-password', function () {
    return view('auth/forgot-password');
});
Route::post('forgot-password', [UserController::class, 'forgotPassword']);
