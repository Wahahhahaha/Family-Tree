<?php

use Illuminate\Support\Facades\Route;


Route::get('/', 'App\Http\Controllers\Ctrl@home');
Route::get('/login', 'App\Http\Controllers\Ctrl@login');
Route::post('/login', 'App\Http\Controllers\Ctrl@authenticate');
Route::post('/logout', 'App\Http\Controllers\Ctrl@logout');
Route::get('/account', 'App\Http\Controllers\Ctrl@account');


Route::get('/management/users', 'App\Http\Controllers\Ctrl@userManagement');
Route::post('/management/users/store', 'App\Http\Controllers\Ctrl@storeUser');
Route::post('/management/users/{userid}/reset-password', 'App\Http\Controllers\Ctrl@resetUserPassword');
Route::post('/management/users/{userid}/delete', 'App\Http\Controllers\Ctrl@deleteUser');
Route::post('/family/profile', 'App\Http\Controllers\Ctrl@updateFamilyProfile');
Route::post('/family/member/store', 'App\Http\Controllers\Ctrl@storeFamilyMemberFromHome');
Route::get('/setting', 'App\Http\Controllers\Ctrl@systemSetting');
Route::post('/setting', 'App\Http\Controllers\Ctrl@updateSystemSetting');
