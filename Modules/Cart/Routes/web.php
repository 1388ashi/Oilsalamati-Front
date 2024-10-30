<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\Customer\CartController as CustomerCartController;

Route::prefix('/cart')->name('cart.')->middleware('auth')->group(function () {
  Route::get('/', [CustomerCartController::class, 'index'])->name('index');
  Route::post('/add/{variety}', [CustomerCartController::class, 'add'])->name('add');
  Route::delete('/{cart}', [CustomerCartController::class, 'remove'])->name('remove');
  Route::put('/{cart}', [CustomerCartController::class, 'update'])->name('update');
});

Route::post('check_free_shipping', [CustomerCartController::class, 'checkFreeShipping']);
