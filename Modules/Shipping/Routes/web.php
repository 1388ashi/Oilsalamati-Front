<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\Admin\ShippingController;
use Modules\Shipping\Http\Controllers\Admin\ShippingRangeController;

Route::webSuperGroup('admin', function () {
    Route::prefix('/shippings')->name('shippings.')->group(function () {

        Route::get('/get-shippable/{address}', [ShippingController::class, 'getShippableForAddress'])->name('getShippableForAddress');
        Route::get('/', [ShippingController::class, 'index'])->name('index');
        Route::get('/create', [ShippingController::class, 'create'])->name('create');
        Route::post('/', [ShippingController::class, 'store'])->name('store');
        Route::get('/{shipping}/edit', [ShippingController::class, 'edit'])->name('edit');
        Route::put('/{shipping}', [ShippingController::class, 'update'])->name('update');
        Route::delete('/{shipping}', [ShippingController::class, 'destroy'])->name('destroy');
        Route::get('/{shipping}', [ShippingController::class, 'show'])->name('show');

    });

    Route::prefix('/shipping-ranges')->name('shipping-ranges.')->group(function () {

        Route::get('/{shipping}/ranges', [ShippingController::class, 'ranges'])->name('index');
        Route::post('/', [ShippingRangeController::class, 'store'])->name('store');
        Route::put('/{shipping_range}', [ShippingRangeController::class, 'update'])->name('update');
        Route::delete('/{shipping_range}', [ShippingRangeController::class, 'destroy'])->name('destroy');

    });

});

Route::webSuperGroup('customer', function() {
    Route::prefix('/shippings')->name('shippings.')->group(function () {
        Route::get('/get-shippable/{address}', [ShippingController::class, 'getShippableForAddress'])->name('getShippableForAddress');
    });
});