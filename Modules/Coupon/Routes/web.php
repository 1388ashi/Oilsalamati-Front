<?php

use Illuminate\Support\Facades\Route;
use Modules\Coupon\Http\Controllers\Admin\CouponController;

Route::webSuperGroup('admin', function () {

  Route::prefix('/coupons')->name('coupons.')->group(function() {

    Route::get('/', [CouponController::class, 'index'])->name('index')->middleware('permission:read_coupon');

    Route::get('/create', [CouponController::class, 'create'])->name('create')->middleware('permission:write_coupon');
    
    Route::get('/{coupon}', [CouponController::class, 'show'])->name('show')->middleware('permission:read_coupon');


    Route::post('/', [CouponController::class, 'store'])->name('store')->middleware('permission:write_coupon');

    Route::get('/{coupon}/edit', [CouponController::class, 'edit'])->name('edit')->middleware('permission:modify_coupon');

    Route::put('/{coupon}', [CouponController::class, 'update'])->name('update')->middleware('permission:modify_coupon');

    Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy')->middleware('permission:delete_coupon');

  });
  
});