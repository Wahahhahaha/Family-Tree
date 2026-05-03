<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\FamilyTreeController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\WikiController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LiveLocationController;
use App\Http\Controllers\LeaderSuccessionController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\Management\RelationshipValidationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;

Route::middleware([\App\Http\Middleware\SetLocaleFromSession::class, \App\Http\Middleware\LogActivityRequests::class])->group(function () {

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// --- Authentication (AuthController) ---
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/login/google', [AuthController::class, 'redirectToGoogleLogin']);
Route::get('/login/google/callback', [AuthController::class, 'handleGoogleLoginCallback']);
Route::post('/login/otp/send', [AuthController::class, 'sendLoginOtp']);
Route::post('/login/otp/verify', [AuthController::class, 'verifyLoginOtp']);
Route::get('/login/otp/resend', [AuthController::class, 'resendLoginOtp']);

// --- Password Recovery (PasswordController) ---
Route::get('/forgot-password', [PasswordController::class, 'forgotPassword']);
Route::get('/forgot-password/phone', [PasswordController::class, 'forgotPasswordPhone']);
Route::post('/forgot-password', [PasswordController::class, 'sendPasswordResetLink']);
Route::post('/forgot-password/phone/send-otp', [PasswordController::class, 'sendPhoneResetOtp']);
Route::post('/forgot-password/phone/verify-otp', [PasswordController::class, 'verifyPhoneResetOtp']);
Route::get('/reset-password/phone', [PasswordController::class, 'resetPasswordPhoneForm']);
Route::post('/reset-password/phone', [PasswordController::class, 'updatePasswordByPhone']);
Route::get('/reset-password/{token}', [PasswordController::class, 'resetPasswordForm']);
Route::post('/reset-password/{token}', [PasswordController::class, 'updatePassword']);
Route::get('/password-reset/success', [PasswordController::class, 'passwordResetSuccess']);

// --- Profile & Account ---
Route::get('/account', [ProfileController::class, 'account']);
Route::post('/account/profile', [ProfileController::class, 'updateEmployerProfile']);
Route::post('/account/password', [ProfileController::class, 'changeAccountPassword']);
Route::post('/employer/change-email', [ProfileController::class, 'requestEmployerChangeEmail']);
Route::post('/employer/change-email/cancel', [ProfileController::class, 'cancelEmployerPendingEmailChange']);
Route::get('/employer/verify-email/{token}', [ProfileController::class, 'verifyEmployerEmailChange']);
Route::post('/employer/change-phone/send-otp', [ProfileController::class, 'requestEmployerPhoneChangeOtp']);
Route::post('/employer/change-phone/verify-otp', [ProfileController::class, 'verifyEmployerPhoneChangeOtp']);
Route::post('/employer/change-phone/cancel', [ProfileController::class, 'cancelEmployerPendingPhoneChange']);

// --- Family Tree ---
Route::get('/', [FamilyTreeController::class, 'home']);
Route::get('/wiki', [WikiController::class, 'index']);
Route::get('/member/{id}/wiki', [WikiController::class, 'show']);
Route::get('/member/{id}/wiki/edit', [WikiController::class, 'edit']);
Route::post('/member/{id}/wiki', [WikiController::class, 'update']);
Route::post('/member/{id}/wiki/upload-doc', [WikiController::class, 'uploadDoc']);
Route::post('/medical-history/store', [WikiController::class, 'storeMedicalHistory']);
Route::post('/medical-history/{historyId}/update', [WikiController::class, 'updateMedicalHistory']);
Route::post('/medical-history/{historyId}/delete', [WikiController::class, 'deleteMedicalHistory']);
Route::get('/calendar', [CalendarController::class, 'index']);
Route::get('/events', [EventController::class, 'index']);
Route::post('/events/rsvp', [EventController::class, 'rsvp']);
Route::post('/events/store', [EventController::class, 'store']);
Route::get('/live-location', [LiveLocationController::class, 'index']);
Route::post('/live-location/update', [LiveLocationController::class, 'update']);
Route::get('/leader-succession', [LeaderSuccessionController::class, 'index']);
Route::post('/leader-succession/heir', [LeaderSuccessionController::class, 'storeHeir']);
Route::post('/leader-succession/pin', [LeaderSuccessionController::class, 'updatePin']);
Route::get('/letters', [LetterController::class, 'index']);
Route::get('/letters/create', [LetterController::class, 'create']);
Route::post('/letters/store', [LetterController::class, 'store']);
Route::get('/letters/{id}', [LetterController::class, 'show']);
Route::get('/gallery', [GalleryController::class, 'index']);
Route::post('/gallery/albums', [GalleryController::class, 'storeAlbum']);
Route::post('/gallery/albums/{albumId}/update', [GalleryController::class, 'updateAlbum']);
Route::post('/gallery/albums/{albumId}/delete', [GalleryController::class, 'destroyAlbum']);
Route::post('/gallery/photos', [GalleryController::class, 'storePhoto']);
Route::get('/gallery/photos/{photoId}', [GalleryController::class, 'showPhoto']);
Route::get('/gallery/photos/{photoId}/file', [GalleryController::class, 'servePhotoFile']);
Route::post('/gallery/photos/{photoId}/update', [GalleryController::class, 'updatePhoto']);
Route::post('/gallery/photos/{photoId}/delete', [GalleryController::class, 'destroyPhoto']);
Route::get('/timeline', function () {
    return redirect('/wiki');
});
Route::post('/timeline/store', [\App\Http\Controllers\FamilyTimelineLiveController::class, 'store']);
Route::post('/timeline/{timelineId}/update', [\App\Http\Controllers\FamilyTimelineLiveController::class, 'update']);
Route::post('/timeline/{timelineId}/delete', [\App\Http\Controllers\FamilyTimelineLiveController::class, 'destroy']);
Route::post('/chatbot/ask', [ChatbotController::class, 'askChatbotAi']);
Route::post('/family/profile', [FamilyTreeController::class, 'updateFamilyProfile']);
Route::post('/family/change-email', [FamilyTreeController::class, 'requestChangeEmail']);
Route::post('/family/change-email/cancel', [FamilyTreeController::class, 'cancelPendingEmailChange']);
Route::get('/family/verify-email/{token}', [FamilyTreeController::class, 'verifyEmailChange']);
Route::post('/family/change-phone/send-otp', [FamilyTreeController::class, 'requestPhoneChangeOtp']);
Route::post('/family/change-phone/verify-otp', [FamilyTreeController::class, 'verifyPhoneChangeOtp']);
Route::post('/family/change-phone/cancel', [FamilyTreeController::class, 'cancelPendingPhoneChange']);
Route::post('/family/member/store', [FamilyTreeController::class, 'storeFamilyMemberFromHome']);
Route::post('/relationship-validations/store', [RelationshipValidationController::class, 'store']);
Route::post('/family/member/delete', [RelationshipValidationController::class, 'store']);
Route::post('/family/member/divorce', [RelationshipValidationController::class, 'store']);
Route::post('/family/member/delete-partner', [RelationshipValidationController::class, 'store']);
Route::post('/family/member/life-status', [FamilyTreeController::class, 'updateFamilyMemberLifeStatus']);
Route::post('/family/member/child-parenting-mode', [FamilyTreeController::class, 'updateChildParentingModeFromHome']);

Route::get('/management/users', [ManagementController::class, 'userManagement']);
Route::get('/management/activity-log', [\App\Http\Controllers\Management\ActivityLogController::class, 'index']);
Route::get('/management/recycle-bin', [\App\Http\Controllers\Management\MaintenanceController::class, 'recycleBin']);
Route::get('/management/data-master', [\App\Http\Controllers\Management\SocialMediaController::class, 'index']);
Route::post('/management/social-media', [\App\Http\Controllers\Management\SocialMediaController::class, 'storeSocial']);
Route::post('/management/social-media/{id}/update', [\App\Http\Controllers\Management\SocialMediaController::class, 'updateSocial']);
Route::post('/management/social-media/{id}/delete', [\App\Http\Controllers\Management\SocialMediaController::class, 'destroySocial']);
Route::post('/management/levels', [\App\Http\Controllers\Management\SocialMediaController::class, 'storeLevel']);
Route::post('/management/levels/{id}/update', [\App\Http\Controllers\Management\SocialMediaController::class, 'updateLevel']);
Route::post('/management/levels/{id}/delete', [\App\Http\Controllers\Management\SocialMediaController::class, 'destroyLevel']);
Route::post('/management/roles', [\App\Http\Controllers\Management\SocialMediaController::class, 'storeRole']);
Route::post('/management/roles/{id}/update', [\App\Http\Controllers\Management\SocialMediaController::class, 'updateRole']);
Route::post('/management/roles/{id}/delete', [\App\Http\Controllers\Management\SocialMediaController::class, 'destroyRole']);
Route::post('/management/data-master/restore/{type}/{id}', [\App\Http\Controllers\Management\SocialMediaController::class, 'restoreMaster']);
Route::post('/management/data-master/force-delete/{type}/{id}', [\App\Http\Controllers\Management\SocialMediaController::class, 'forceDeleteMaster']);

// --- Management / Superadmin ---
Route::get('/management/users/export', [ManagementController::class, 'exportUsers']);
Route::post('/management/users/import', [ManagementController::class, 'importUsers']);
Route::get('/management/backup-database', [\App\Http\Controllers\Management\MaintenanceController::class, 'backupDatabase']);
Route::get('/management/console', [\App\Http\Controllers\Management\MaintenanceController::class, 'console']);
Route::post('/management/console/run', [\App\Http\Controllers\Management\MaintenanceController::class, 'runConsole']);
Route::get('/management/validation', [RelationshipValidationController::class, 'index']);
Route::get('/management/validation/{validationId}/document', [RelationshipValidationController::class, 'document']);
Route::post('/management/validation/{validationId}/approve', [RelationshipValidationController::class, 'approve']);
Route::post('/management/validation/{validationId}/reject', [RelationshipValidationController::class, 'reject']);
Route::post('/management/backup-database/export', [ManagementController::class, 'exportDatabaseBackup']);
Route::post('/management/backup-database/import', [ManagementController::class, 'importDatabaseBackup']);
Route::get('/management/permission', [\App\Http\Controllers\Management\SystemSettingsController::class, 'permission']);
Route::post('/management/permission', [ManagementController::class, 'updatePermissionSetting']);
Route::post('/management/users/store', [ManagementController::class, 'storeUser']);
Route::post('/management/users/{userid}/update', [ManagementController::class, 'updateUser']);
Route::post('/management/users/bulk-delete', [ManagementController::class, 'bulkDeleteUsers']);
Route::post('/management/users/bulk-force-delete', [ManagementController::class, 'bulkForceDeleteUsers']);
Route::post('/management/users/{userid}/reset-password', [ManagementController::class, 'resetUserPassword']);
Route::post('/management/users/{userid}/delete', [ManagementController::class, 'deleteUser']);
Route::post('/management/users/{userid}/restore', [ManagementController::class, 'restoreUser']);
Route::post('/management/users/{userid}/force-delete', [ManagementController::class, 'forceDeleteUser']);
Route::post('/management/users/life-status', [ManagementController::class, 'updateLifeStatus']);
Route::get('/management/setting', [\App\Http\Controllers\Management\SystemSettingsController::class, 'index'])->name('management.setting');
Route::post('/management/setting', [ManagementController::class, 'updateSystemSetting'])->name('management.setting.save');
Route::post('/management/setting/landing', [\App\Http\Controllers\Management\SystemSettingsController::class, 'updateLandingPageSettings'])->name('management.setting.landing.save');
Route::get('/setting', function () {
    return redirect('/management/setting');
});
Route::post('/setting', [ManagementController::class, 'updateSystemSetting']);

});
