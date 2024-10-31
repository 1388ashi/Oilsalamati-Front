<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\Customer\OrderController as CustomerOrderController;
use Modules\Order\Http\Controllers\Admin\OrderController;
use Modules\Order\Http\Controllers\Admin\OrderGiftRangeController;
use Modules\Order\Http\Controllers\Admin\ShippingExcelController;
use Modules\Order\Http\Controllers\OrderUpdaterServiceController;

Route::webSuperGroup('admin', function () {

  Route::prefix('/orders')->name('orders.')->group(function () {
    Route::put('/{order}/update-status', [OrderController::class,'updateStatus'])->name('update-status');
    Route::post('/status/changes', [OrderController::class,'changeStatusSelectedOrders'])->name('changeStatusSelectedOrders');
    Route::get('/', [OrderController::class, 'index'])->name('index');
    Route::get('/create', [OrderController::class, 'create'])->name('create');
    Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    Route::post('/', [OrderController::class, 'store'])->name('store');
    Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
    Route::put('/{order}', [OrderController::class, 'update'])->name('update');
    Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
    Route::post('print/details', [OrderController::class, 'detailsOrderForPrint'])->name('print');
  });

  Route::get('new_orders', [OrderController::class,'todayOrders'])->name('new-orders.index');

  Route::prefix('/order-gift-ranges')->name('order-gift-ranges.')->group(function() {
    Route::get('/', [OrderGiftRangeController::class, 'index'])->name('index');
    Route::get('/create', [OrderGiftRangeController::class, 'create'])->name('create');
    Route::post('/', [OrderGiftRangeController::class, 'store'])->name('store');
    Route::get('/{order_gift_range}/edit', [OrderGiftRangeController::class, 'edit'])->name('edit');
    Route::put('/{order_gift_range}', [OrderGiftRangeController::class, 'update'])->name('update');
    Route::delete('/{order_gift_range}', [OrderGiftRangeController::class, 'destroy'])->name('destroy');
  });

  Route::prefix('/shipping-excels')->name('shipping-excels.')->group(function() {
    Route::delete('/multiple-delete', [ShippingExcelController::class, 'multipleDelete'])->name('multiple-delete');
    Route::get('/', [ShippingExcelController::class, 'index'])->name('index');
    Route::post('/', [ShippingExcelController::class, 'store'])->name('store');
    Route::delete('/{shipping_excel}', [ShippingExcelController::class, 'destroy'])->name('destroy');
  });

  Route::prefix('/orderUpdater/{order_id}')->name('orderUpdater.')->group(function() {
    Route::put('/', [OrderUpdaterServiceController::class, 'applier'])->name('applier');
    Route::post('/showcase', [OrderUpdaterServiceController::class, 'showcase'])->name('showcase');
  });

});

Route::webSuperGroup('customer', function() {
  Route::prefix('orders')->name('orders.')->group(function() {
    Route::post('/', [CustomerOrderController::class, 'store'])->name('store');
  });
});
