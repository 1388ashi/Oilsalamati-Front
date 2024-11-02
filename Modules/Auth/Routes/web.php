<?php
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Admin\AuthController as AdminAuthController;
use Modules\Auth\Http\Controllers\Customer\AuthController as CustomerAuthController;


Route::webSuperGroup('admin', function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('form');
    Route::post('/login' , [AdminAuthController::class, 'webLogin'])->name('webLogin');
    Route::post('/webLogout', [AdminAuthController::class, 'webLogout'])->name('webLogout');
}, []);

Route::webSuperGroup('customer', function () {
    Route::get('/login/{mobile}', [CustomerAuthController::class, 'showLoginForm'])->name('showLoginForm');
    Route::post('/login' , [CustomerAuthController::class, 'webLogin'])->name('login');
    Route::post('/logout' , [CustomerAuthController::class, 'webLogout'])->name('logout');
}, []);
Route::get('/register-login/{mobile?}' , [CustomerAuthController::class, 'webRegisterLogin'])->name('pageRegisterLogin');
Route::post('/register/customer/{mobile?}' , [CustomerAuthController::class, 'registerLogin'])->name('registerLogin');
Route::get('send-sms/{mobile}/{type?}', [CustomerAuthController::class, 'webSendSms'])->name('webSendSms');  
Route::get('send-sms-register/{mobile}', [CustomerAuthController::class, 'webSendSmsRegister'])->name('webSendSmsRegister');  
Route::post('/verify/customer' , [CustomerAuthController::class, 'register'])->name('register');
Route::post('/password/customer' , [CustomerAuthController::class, 'createPassword'])->name('createPassword');
Route::get('/forget-password/{mobile}' , [CustomerAuthController::class, 'webResetPassword'])->name('pageRestsPassword');
Route::post('/forget-password' , [CustomerAuthController::class, 'resetPassword'])->name('resetPassword');
