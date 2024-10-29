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
    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('showLoginForm');
    Route::post('/login' , [CustomerAuthController::class, 'webLogin'])->name('login');
}, []);
