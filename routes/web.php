<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Lecturer\LecturerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Student\ChatbotController;
use App\Http\Controllers\Student\CodeLabController;
use App\Http\Controllers\Student\GameController;
use App\Http\Controllers\Student\LearningController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| iSEP - Laluan web
|--------------------------------------------------------------------------
| Setiap laluan menggantikan satu fail .php dalam sistem asal.
*/

// ---------- Awam ----------
Route::get('/', [HomeController::class, 'index'])->name('home')
    ->middleware('cache.headers:no_store;no_cache;must_revalidate;max_age=0');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login')
        ->middleware('cache.headers:no_store;no_cache;must_revalidate;max_age=0');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/reset-password', [AuthController::class, 'showReset'])->name('password.reset');

// ---------- Pelajar ----------
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [LearningController::class, 'dashboard'])->name('dashboard');
    Route::get('/language/{slug}', [LearningController::class, 'language'])->name('language');
    Route::get('/enroll/{slug}', [LearningController::class, 'showEnroll'])->name('enroll');
    Route::post('/enroll/{slug}', [LearningController::class, 'enroll']);
    Route::get('/chapter/{id}', [LearningController::class, 'chapter'])->whereNumber('id')->name('chapter');
    Route::post('/chapter/{id}', [LearningController::class, 'chapterAction'])->whereNumber('id');
    Route::get('/certificates', [LearningController::class, 'certificates'])->name('certificates');
    Route::get('/certificate/{code}', [LearningController::class, 'certificate'])->name('certificate');
    Route::get('/leaderboard', [LearningController::class, 'leaderboard'])->name('leaderboard');

    Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [StudentProfileController::class, 'update']);
    Route::get('/shop', [ShopController::class, 'show'])->name('shop');
    Route::post('/shop', [ShopController::class, 'action']);

    Route::get('/games', [GameController::class, 'index'])->name('games');
    Route::get('/games/chess', [GameController::class, 'chess'])->name('chess');
    Route::get('/games/dam', [GameController::class, 'dam'])->name('dam');
    Route::match(['get', 'post'], '/games/api', [GameController::class, 'api'])->name('games.api');

    Route::get('/report', [ReportController::class, 'show'])->name('report');
    Route::post('/report', [ReportController::class, 'store']);

    Route::post('/chatbot', ChatbotController::class)->name('chatbot');
    Route::post('/code/run', [CodeLabController::class, 'run'])->name('code.run');
    Route::post('/code/assist', [CodeLabController::class, 'assist'])->name('code.assist');
});

// ---------- Admin ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/languages', [AdminController::class, 'languages'])->name('languages');
    Route::post('/languages', [AdminController::class, 'languagesAction']);
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'usersAction']);
    Route::get('/feedback', [AdminController::class, 'feedback'])->name('feedback');
    Route::post('/feedback', [AdminController::class, 'feedbackAction']);
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::get('/backfill-badges', [AdminController::class, 'backfillBadges'])->name('backfill');
});

// ---------- Urus kandungan (admin & pensyarah) ----------
Route::middleware(['auth', 'role:admin,lecturer'])->prefix('manage')->name('manage.')->group(function () {
    Route::get('/languages/{languageId}/chapters', [ContentController::class, 'chapters'])->whereNumber('languageId')->name('chapters');
    Route::post('/languages/{languageId}/chapters', [ContentController::class, 'chaptersAction'])->whereNumber('languageId');
    Route::get('/chapters/{chapterId}/content', [ContentController::class, 'content'])->whereNumber('chapterId')->name('content');
    Route::post('/chapters/{chapterId}/content', [ContentController::class, 'contentAction'])->whereNumber('chapterId');
    Route::get('/exercise-template.csv', [ContentController::class, 'csvTemplate'])->name('csv_template');
});

// ---------- Pensyarah ----------
Route::middleware(['auth', 'role:lecturer'])->prefix('lecturer')->name('lecturer.')->group(function () {
    Route::get('/dashboard', [LecturerController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [LecturerController::class, 'reports'])->name('reports');
    Route::get('/report', [ReportController::class, 'show'])->name('report');
    Route::post('/report', [ReportController::class, 'store']);
    Route::get('/profile', [LecturerController::class, 'profile'])->name('profile');
    Route::post('/profile', [LecturerController::class, 'updateProfile']);
});
