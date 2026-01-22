<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TeacherController;

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

// Teacher
Route::middleware(['auth', 'isTeacher'])->group(function () {
    Route::get('/teacher', [TeacherController::class, 'index'])->name('teacher.index');

    // Classroom view
    Route::get('/teacher/classes/{classroom}/view', [TeacherController::class, 'classView'])
        ->name('teacher.classes.view');

    // Students in classroom
    Route::get('/teacher/classes/{classroom}/students', [TeacherController::class, 'classStudents'])
        ->name('teacher.classes.students');

    // Reading list
    Route::get('/teacher/classes/{classroom}/reading-list', [TeacherController::class, 'classReadingList'])
        ->name('teacher.classes.readingList');

    // Create class
    Route::get('/teacher/classes/create', [TeacherController::class, 'createClass'])
        ->name('teacher.classes.create');
    Route::post('/teacher/classes/create', [TeacherController::class, 'storeClass'])
        ->name('teacher.classes.store');

    // Add Students
    Route::get('/teacher/classes/{classroom}/addStudents', [TeacherController::class, 'addStudents'])
        ->name('teacher.classes.addStudents');
    
    // Create Students
    Route::post('/teacher/classes/{classroom}/addStudents', [TeacherController::class, 'storeStudents'])
        ->name('teacher.classes.storeStudents');
});

// Export student list CSV
Route::get('/teacher/classes/{classroom}/export-students', [TeacherController::class, 'exportStudents'])
    ->name('teacher.classes.export');

// user profile, anyone logged in can visit
Route::get('/user/{id}', [UserController::class, 'show'])->name('user.show')->middleware('auth');
// get own profile
Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile')->middleware('auth');
