<?php

use Illuminate\Support\Facades\Route;
use Modules\Flash\Http\Controllers\Admin\FlashController;

Route::webSuperGroup('admin', function () {

	Route::prefix('/flashes')->name('flashes.')->group(function () {

		Route::get('/', [FlashController::class, 'index'])->name('index')->middleware('permission:read_flash');

		Route::get('/create', [FlashController::class, 'create'])->name('create')->middleware('permission:write_flash');

		Route::get('/{flash}', [FlashController::class, 'show'])->name('show')->middleware('permission:read_flash');

		Route::post('/', [FlashController::class, 'store'])->name('store')->middleware('permission:write_flash');

		Route::get('/{flash}/edit', [FlashController::class, 'edit'])->name('edit')->middleware('permission:modify_flash');

		Route::put('/{flash}', [FlashController::class, 'update'])->name('update')->middleware('permission:modify_flash');

		Route::delete('/{flash}', [FlashController::class, 'destroy'])->name('destroy')->middleware('permission:delete_flash');
	});
});
