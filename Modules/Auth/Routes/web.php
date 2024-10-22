<?php
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Admin\AuthController;


Route::webSuperGroup('admin', function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('form');
    Route::post('/login' , [AuthController::class, 'webLogin'])->name('webLogin');
    Route::post('/webLogout', [AuthController::class, 'webLogout'])->name('webLogout');
}, []);
