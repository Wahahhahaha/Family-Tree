<?php

use Illuminate\Support\Facades\Route;


Route::get('/', 'App\Http\Controllers\Ctrl@home');
Route::get('/login', 'App\Http\Controllers\Ctrl@login');
Route::post('/login', 'App\Http\Controllers\Ctrl@authenticate');
Route::get('/forgot-password', 'App\Http\Controllers\Ctrl@forgotPassword');
Route::get('/forgot-password/phone', 'App\Http\Controllers\Ctrl@forgotPasswordPhone');
Route::post('/forgot-password', 'App\Http\Controllers\Ctrl@sendPasswordResetLink');
Route::post('/forgot-password/phone/send-otp', 'App\Http\Controllers\Ctrl@sendPhoneResetOtp');
Route::post('/forgot-password/phone/verify-otp', 'App\Http\Controllers\Ctrl@verifyPhoneResetOtp');
Route::get('/reset-password/phone', 'App\Http\Controllers\Ctrl@resetPasswordPhoneForm');
Route::post('/reset-password/phone', 'App\Http\Controllers\Ctrl@updatePasswordByPhone');
Route::get('/reset-password/{token}', 'App\Http\Controllers\Ctrl@resetPasswordForm');
Route::post('/reset-password/{token}', 'App\Http\Controllers\Ctrl@updatePassword');
Route::get('/password-reset/success', 'App\Http\Controllers\Ctrl@passwordResetSuccess');
Route::post('/logout', 'App\Http\Controllers\Ctrl@logout');
Route::get('/account', 'App\Http\Controllers\Ctrl@account');
Route::get('/chatbot', 'App\Http\Controllers\Ctrl@chatbot');
Route::post('/account/profile', 'App\Http\Controllers\Ctrl@updateEmployerProfile');


Route::get('/management/users', 'App\Http\Controllers\Ctrl@userManagement');
Route::get('/management/activity-log', 'App\Http\Controllers\Ctrl@activityLog');
Route::get('/management/recycle-bin', 'App\Http\Controllers\Ctrl@recycleBin');
Route::post('/management/users/store', 'App\Http\Controllers\Ctrl@storeUser');
Route::post('/management/users/{userid}/reset-password', 'App\Http\Controllers\Ctrl@resetUserPassword');
Route::post('/management/users/{userid}/delete', 'App\Http\Controllers\Ctrl@deleteUser');
Route::post('/family/profile', 'App\Http\Controllers\Ctrl@updateFamilyProfile');
Route::post('/family/member/store', 'App\Http\Controllers\Ctrl@storeFamilyMemberFromHome');
Route::post('/family/member/delete', 'App\Http\Controllers\Ctrl@deleteFamilyMemberFromHome');
Route::post('/family/member/life-status', 'App\Http\Controllers\Ctrl@updateFamilyMemberLifeStatus');
Route::get('/setting', 'App\Http\Controllers\Ctrl@systemSetting');
Route::post('/setting', 'App\Http\Controllers\Ctrl@updateSystemSetting');
