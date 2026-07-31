<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InheritanceController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\LiveLocationController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecycleBinController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\WikiController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::middleware('auth')->group(function () {
    // User Account & Profile
    Route::get('/account', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/account/update-employer', [ProfileController::class, 'updateEmployer'])->name('profile.employer.update');
    Route::post('/profile/family', [ProfileController::class, 'updateFamilyMember'])->name('profile.family.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // System & Landing Page Management
    Route::get('/management/system', [SystemController::class, 'index'])->name('system.index');
    Route::post('/management/system/global', [SystemController::class, 'updateGlobal'])->name('system.global.update');
    Route::post('/management/system/landing', [SystemController::class, 'updateLanding'])->name('system.landing.update');

    // User Management
    Route::get('/management/users', [UserController::class, 'index'])->name('users.index');

    Route::get('/management/recycle-bin', [RecycleBinController::class, 'index'])->name('recycle-bin.index');
    Route::post('/management/recycle-bin/{id}/restore', [RecycleBinController::class, 'restore'])->name('recycle-bin.restore');
    Route::delete('/management/recycle-bin/{id}/permanent', [RecycleBinController::class, 'permanentDelete'])->name('recycle-bin.permanent');
    Route::post('/management/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/management/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('/management/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/management/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // Database Backup
    Route::get('/management/backup', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/management/backup/run', [BackupController::class, 'run'])->name('backup.run');
    Route::get('/management/backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('/management/backup/{filename}', [BackupController::class, 'destroy'])->name('backup.destroy');

    // Master Data
    Route::get('/management/master-data', [MasterDataController::class, 'index'])->name('master-data.index');
    Route::post('/management/master-data/social-media', [MasterDataController::class, 'storeSocialMedia'])->name('master-data.social-media.store');
    Route::put('/management/master-data/social-media/{id}', [MasterDataController::class, 'updateSocialMedia'])->name('master-data.social-media.update');
    Route::delete('/management/master-data/social-media/{id}', [MasterDataController::class, 'destroySocialMedia'])->name('master-data.social-media.destroy');

    // Family Features
    Route::get('/wiki', [WikiController::class, 'index'])->name('wiki.index');
    Route::get('/wiki/member/{id}', [WikiController::class, 'show'])->name('wiki.show');

    Route::get('/letters', [LetterController::class, 'index'])->name('letters.index');
    Route::post('/letters', [LetterController::class, 'store'])->name('letters.store');
    Route::post('/letters/{id}/read', [LetterController::class, 'markAsRead'])->name('letters.read');

    Route::get('/live-location', [LiveLocationController::class, 'index'])->name('live-location.index');
    Route::post('/live-location/update', [LiveLocationController::class, 'update'])->name('live-location.update');

    Route::get('/validation', [ValidationController::class, 'index'])->name('validation.index');
    Route::post('/validation/{id}/status', [ValidationController::class, 'updateStatus'])->name('validation.update-status');
    Route::get('/inheritance', [InheritanceController::class, 'index'])->name('inheritance.index');
    Route::post('/inheritance/set-heir', [InheritanceController::class, 'setHeir'])->name('inheritance.set-heir');
    Route::post('/inheritance/reset-pin', [InheritanceController::class, 'resetPin'])->name('inheritance.reset-pin');

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions/update', [PermissionController::class, 'update'])->name('permissions.update');

    Route::put('/family-members/{id}', [FamilyMemberController::class, 'update'])->name('family-members.update');
    Route::post('/family-members/{id}/mark-as-deceased', [FamilyMemberController::class, 'markAsDeceased'])->name('family-members.mark-as-deceased');
    Route::post('/family-members/{id}/request-deletion', [FamilyMemberController::class, 'requestDeletion'])->name('family-members.request-deletion');
    Route::delete('/family-members/{id}', [FamilyMemberController::class, 'destroy'])->name('family-members.destroy');
    Route::post('/family-members', [FamilyMemberController::class, 'store'])->name('family-members.store');

    Route::get('/management/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/galleries', [GalleryController::class, 'index'])->name('gallery.index');
    Route::post('/galleries/album', [GalleryController::class, 'storeAlbum'])->name('gallery.album.store');
    Route::get('/galleries/{album}', [GalleryController::class, 'show'])->name('gallery.show');
    Route::post('/galleries/{album}/upload', [GalleryController::class, 'uploadPhoto'])->name('gallery.photo.upload');
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{event}/respond', [EventController::class, 'respond'])->name('events.respond');
    
    // Chatbot
    Route::post('/chatbot/respond', [ChatbotController::class, 'respond'])->name('chatbot.respond');
});

// Auth Routes
Route::get('/login', [LoginController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/login/otp', function () {
    return Inertia::render('Auth/OtpLogin', [
        'translations' => trans('login')
    ]);
})->name('login.otp')->middleware('guest');

Route::get('/login/google', [GoogleController::class, 'redirect'])->name('login.google')->middleware('guest');
Route::get('/login/google/callback', [GoogleController::class, 'callback'])->middleware('guest');

Route::post('/login/otp/send', [OtpController::class, 'send'])->name('login.otp.send')->middleware('guest');
Route::post('/login/otp/verify', [OtpController::class, 'verify'])->name('login.otp.verify')->middleware('guest');
