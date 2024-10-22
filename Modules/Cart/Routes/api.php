<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\Admin\CartController;
use Modules\Cart\Http\Controllers\Customer\CartController as CustomerCartController;
use Modules\Cart\Http\Controllers\All\CartController as AllCartController;


Route::superGroup('admin' ,  function() {
    Route::get('cart/' , [CartController::class , 'index'])->name('cart.index');
    Route::post('cart/add/{variety}' , [CartController::class , 'add'])->name('cart.add');
    Route::delete('cart/{cart}' , [CartController::class , 'remove'])->name('cart.remove');
    Route::put('cart/{cart}' , [CartController::class , 'update'])->name('cart.update');
});

Route::superGroup('customer' ,  function() {
    Route::get('cart/' , [CustomerCartController::class , 'index'])->name('cart.index');
    Route::post('cart/add/{variety}' , [CustomerCartController::class , 'add'])->name('cart.add');
    Route::delete('cart/{cart}' , [CustomerCartController::class , 'remove'])->name('cart.remove');
    Route::put('cart/{cart}' , [CustomerCartController::class , 'update'])->name('cart.update');

    Route::post('check_free_shipping' , [CustomerCartController::class , 'checkFreeShipping']);
});

# Route For All

Route::superGroup('all' ,  function() {
    Route::get('cart/' , [AllCartController::class , 'index'])->name('cart.index');
    Route::get('cart/get' , [AllCartController::class , 'getCarts'])->name('cart.get');
},[]);
