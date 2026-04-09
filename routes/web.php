<?php

use Illuminate\Support\Facades\Route;


Route::get('/', 'App\Http\Controllers\Ctrl@home');
Route::get('/login', 'App\Http\Controllers\Ctrl@login');
Route::post('/login', 'App\Http\Controllers\Ctrl@authenticate');
Route::get('/forgot-password', 'App\Http\Controllers\Ctrl@forgotPassword');
Route::post('/forgot-password', 'App\Http\Controllers\Ctrl@sendPasswordResetLink');
Route::get('/reset-password/{token}', 'App\Http\Controllers\Ctrl@resetPasswordForm');
Route::post('/reset-password/{token}', 'App\Http\Controllers\Ctrl@updatePassword');
Route::get('/password-reset/success', 'App\Http\Controllers\Ctrl@passwordResetSuccess');
Route::post('/logout', 'App\Http\Controllers\Ctrl@logout');
Route::get('/account', 'App\Http\Controllers\Ctrl@account');


Route::get('/management/users', 'App\Http\Controllers\Ctrl@userManagement');
Route::post('/management/users/store', 'App\Http\Controllers\Ctrl@storeUser');
Route::post('/management/users/{userid}/reset-password', 'App\Http\Controllers\Ctrl@resetUserPassword');
Route::post('/management/users/{userid}/delete', 'A;p\Http\Controllers\Ctrl@deleteUser');
Route::post('/family/profile', 'App\Http\Controllers\Ctrl@updateFamilyProfile');
Route::post('/family/member/store', 'App\Http\Controllers\Ctrl@storeFamilyMemberFromHome');
Route::get('/setting', 'App\Http\Controllers\Ctrl@systemSetting');
Route::post('/setting', 'App\Http\Controllers\Ctrl@updateSystemSetting');

