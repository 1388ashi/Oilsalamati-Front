<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Admin\AdminController;
use Modules\Admin\Http\Controllers\Admin\DashboardController;

Route::webSuperGroup('admin', function () {
  Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

  Route::get('/admins', [AdminController::class,'index'])->name('admins.index');
  Route::get('/admins/create', [AdminController::class,'create'])->name('admins.create');
  Route::post('/admins', [AdminController::class,'store'])->name('admins.store');
  Route::get('/admins/{admin}/edit', [AdminController::class,'edit'])->name('admins.edit');
  Route::put('/admins/{admin}', [AdminController::class,'update'])->name('admins.update');
  Route::delete('/admins/delete/{role}', [AdminController::class,'destroy'])->name('admins.destroy');
});
