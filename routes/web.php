<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\BookReviewController;
use App\Http\Controllers\SchoolAdminController;
use App\Http\Controllers\ProgressController;
use App\Http\Middleware\IsSchoolAdmin;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\UserManageController;

Route::get('/', [SiteController::class, 'index'])->name('index');

// Login
Route::get('/login', [SessionController::class, 'show'])->name('login')->middleware('guest');
Route::post('/login', [SessionController::class, 'create'])->middleware('guest');

// Forgot Password
Route::get('/forgot-password', function () {
    return view('auth/forgot-password');
})->middleware('guest');

// Forgot password page
Route::get('/forgot-password', [UserController::class, 'showForgotPassword'])
    ->name('password.forgot');

// Forgot password form
Route::post('/forgot-password', [UserController::class, 'submitForgotPassword'])
    ->name('password.forgot.submit');


// ==========================================
// AUTHENTICATED ROUTES
// ==========================================
Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    // Explore page
    Route::get('/explore', [ExploreController::class, 'index'])->name('explore'); 

    // Individual books
    Route::get('/books/{id}', [ExploreController::class, 'show'])->name('books.show');

    // Edit book
    Route::put('/books/{book}/update', [ExploreController::class, 'updateBook'])->name('explore.updateBook')->middleware('auth');
    
    Route::post('/explore/books/{book}/request', [ExploreController::class, 'requestBook'])->name('explore.requestBook');
    // favourite toggle
    Route::post('/explore/books/{book}/favourite', [ExploreController::class, 'toggleFavourite'])->name('favourites.toggle');

    // Progress Page
    Route::get('/progress', [ProgressController::class, 'index'])->name('progress');
    Route::patch('/user', [UserController::class, 'update']);
    Route::post('/logout', [SessionController::class, 'destroy']);

    // Leaderboard page
    Route::get('/leaderboard/{classroom?}', [LeaderboardController::class, 'show'])->name('leaderboard');

    // user profile, anyone logged in can visit
    Route::get('/user/{id}', [UserController::class, 'show'])->name('user.show');
    
    // get own profile
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile');

    // Book reviews
    Route::post('/books/reviews/{reviewId}/upvote', [BookReviewController::class, 'upvote'])->name('reviews.upvote');
    Route::get('/books/{id}/review', [BookReviewController::class, 'create']);
    Route::post('/books/{id}/review', [BookReviewController::class, 'store']);
    Route::delete('/books/{bookId}/review/{reviewId}', [BookReviewController::class, 'destroy']);

    // Students announcements
    Route::post('/student/announcements/{id}/hide', [ClassroomController::class, 'hideAnnouncement'])->name('student.announcements.hide');
    Route::post('/student/announcements/restore', [ClassroomController::class, 'restoreAnnouncements'])->name('student.announcements.restore');

    // Manage profile
    Route::get('/users/{user}/manage', [UserManageController::class, 'edit'])
        ->name('user.manage.edit');
    
    // Update profile
    Route::patch('/users/{user}/manage/field', [UserManageController::class, 'updateField'])
        ->name('user.manage.updateField');
    
    // View assignments
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');

    // Mark a book as completed
    Route::post('/assignments/{book}/complete', [AssignmentController::class, 'markCompleted'])->name('assignments.complete');

    // Notify teacher
    Route::post('/assignments/notify', [AssignmentController::class, 'notifyTeacher'])->name('assignments.notify');
});


// ==========================================
// ADMIN ROUTES
// ==========================================
// Register
Route::middleware(['auth', 'isAdmin'])->group(function () {
    Route::get('/register', [UserController::class, 'create']);
    Route::post('/register', [UserController::class, 'store'])->name('register');
});


// ==========================================
// TEACHER ROUTES
// ==========================================
// Teacher
Route::middleware(['auth', 'isTeacher'])->group(function () {
    Route::get('/teacher', [TeacherController::class, 'index'])->name('teacher.index');

    // Classroom view
    Route::get('/teacher/classes/{classroom}/view', [ClassroomController::class, 'classView'])
        ->name('teacher.classes.view');

    // Manage classroom
    Route::get('/teacher/classes/{classroom}/students', [TeacherController::class, 'classStudents'])
        ->name('teacher.classes.students');

    // Reading list
    Route::get('/teacher/classes/{classroom}/reading-list', [ReadingController::class, 'generateList'])
        ->name('teacher.classes.readingList');

    // Create class
    Route::get('/teacher/classes/create', [TeacherController::class, 'createClass'])
        ->name('teacher.classes.create');
    Route::post('/teacher/classes/create', [TeacherController::class, 'storeClass'])
        ->name('teacher.classes.store');

    // Add Students
    Route::get('/teacher/classes/{classroom}/addStudents', [TeacherController::class, 'addStudents'])
        ->name('teacher.classes.addStudents');

    // Transfer student
    Route::delete('/classes/{classroom}/students/{student}/transfer', [TeacherController::class, 'transferStudent'])->name('teacher.classes.transferStudent');

    // Remove Student
    Route::delete('/teacher/classes/{classroom}/students/{studentId}', [TeacherController::class, 'removeStudent'])
        ->name('teacher.classes.removeStudent');

    // Remove All Students
    Route::get('/teacher/classes/{classroom}/removeAllStudents', [TeacherController::class, 'removeAllStudents'])
        ->name('teacher.classes.removeAllStudents');

    // Create Students
    Route::post('/teacher/classes/{classroom}/addStudents', [TeacherController::class, 'storeStudents'])
        ->name('teacher.classes.storeStudents');

    // Manage Students
    Route::get('/user/{user}/manage', [ManageController::class, 'show'])->name('user.manage');

    // Update student profile fields
    Route::patch('/user/{user}/manage/update-field', [ManageController::class, 'updateField'])
        ->name('user.manage.updateField')
        ->middleware(['auth', 'isTeacher']);

    // Export pupil statistics
    Route::get('/user/{user}/manage/export', [ManageController::class, 'exportStatistics'])
    ->name('user.manage.export');

    // Export student list CSV
    Route::get('/teacher/classes/{classroom}/export-students', [TeacherController::class, 'exportStudents'])
        ->name('teacher.classes.export');

    // Import student list CSV - Show form
    Route::get('/teacher/classes/{classroom}/import-students', [TeacherController::class, 'showImportForm'])
        ->name('teacher.classes.import');

    // Import student list CSV - Process upload
    Route::post('/teacher/classes/{classroom}/import-students', [TeacherController::class, 'importStudents'])
        ->name('teacher.classes.importStudents');

    // Delete classroom
    Route::delete('/classes/{classroom}', [ClassroomController::class, 'removeClassroom'])
        ->name('teacher.classes.removeClassroom');

    // Add book
    Route::post('/explore/add', [ExploreController::class, 'addBook'])->name('explore.addBook');

    // Delete book
    Route::delete('/explore/book/{book}', [ExploreController::class, 'deleteBook'])->name('explore.deleteBook');

    // Update book stock
    Route::post('/explore/books/{book}/stock', [ExploreController::class, 'updateStock'])->name('explore.updateStock');
    
    // Reading list
    Route::prefix('teacher/classes/{classroom}/reading')->name('teacher.reading.')->group(function () {
        
        Route::get('/generate', [ReadingController::class, 'generateList'])->name('index');

        Route::post('/generate-all', [ReadingController::class, 'generateAll'])->name('generateAll');
        Route::post('/save-log', [ReadingController::class, 'saveWeeklyLog'])->name('saveWeeklyLog');
        Route::post('/student/{student}/assign', [ReadingController::class, 'assignBook'])->name('assignBook');
    });

    // Archive classroom
    Route::patch('/teacher/classes/{id}/archive', [ClassroomController::class, 'archiveClassroom'])
    ->name('teacher.classes.archiveClassroom');

    // Progress archived classroom
    Route::patch('/teacher/classes/{id}/progress', [ClassroomController::class, 'progressClassroom'])->name('teacher.classes.progressClassroom');
    
    // Restore archived classroom
    Route::patch('/teacher/classes/{id}/restore', [ClassroomController::class, 'restoreClassroom'])->name('teacher.classes.restoreClassroom');
    
    // View archived classroom statistics
    Route::get('/teacher/classes/{id}/stats', [ClassroomController::class, 'showStatistics'])->name('teacher.classes.showStatistics');

    // Export archived classroom CSV
    Route::get('/teacher/classes/{id}/stats/export', [ClassroomController::class, 'exportStatistics'])->name('teacher.classes.exportStatistics');

    // Classroom Announcements
    Route::get('/teacher/classes/{classroom}/announcement', [ClassroomController::class, 'createAnnouncement'])
        ->name('teacher.classes.announcements.create');
    
    Route::post('/teacher/classes/{classroom}/announcement', [ClassroomController::class, 'storeAnnouncement'])
        ->name('teacher.classes.announcements.store');

    // Delete announcement
    Route::delete('/teacher/announcements/{id}', [ClassroomController::class, 'deleteAnnouncement'])
    ->name('teacher.announcements.delete');

});


// ==========================================
// schooladmin ROUTES
// ==========================================
// schooladmin
Route::middleware(['auth', IsSchoolAdmin::class])->group(function () {
    // Banned books
    Route::get('/schooladmin/banned-books', [SchoolAdminController::class, 'bannedBooks'])->name('schooladmin.banned-books');
    Route::post('/schooladmin/banned-books/{book}/toggle', [SchoolAdminController::class, 'toggleBan'])->name('schooladmin.toggle-ban');

    // Create teacher
    Route::get('/schooladmin/teachers/create', [SchoolAdminController::class, 'createTeacher'])->name('schooladmin.teachers.create');
    Route::post('/schooladmin/teachers/create', [SchoolAdminController::class, 'storeTeacher'])->name('schooladmin.teachers.store');

    // Delete teacher
    Route::delete('/schooladmin/teachers/{id}', [App\Http\Controllers\SchoolAdminController::class, 'destroyTeacher'])->name('schooladmin.teachers.destroy');
});