<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\Customer\CartController as CustomerCartController;

Route::prefix('/cart')->name('cart.')->group(function () {
  Route::get('/', [CustomerCartController::class, 'index'])->name('index');
  Route::post('/add/{id?}', [CustomerCartController::class, 'add'])->name('add');  
  Route::delete('/{cart?}', [CustomerCartController::class, 'remove'])->name('remove');
  Route::put('/{id?}', [CustomerCartController::class, 'update'])->name('update');
});

Route::post('check_free_shipping', [CustomerCartController::class, 'checkFreeShipping']);
