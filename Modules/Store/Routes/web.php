<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\Admin\StoreController;
use Modules\Store\Http\Controllers\Admin\StoreTransactionController;

Route::webSuperGroup('admin', function () {
	Route::get('/store-transactions', [StoreTransactionController::class, 'index'])->name('store-transactions')->middleware('permission:read_store');
	Route::post('/store/load-varieties', [StoreController::class, 'loadVarieties'])->name('stores.load-varieties');

	Route::prefix('/stores')->name('stores.')->group(function() {
		Route::post('/', [StoreController::class, 'store'])->name('store');
	});
});
