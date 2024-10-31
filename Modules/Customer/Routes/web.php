<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\Admin\AddressController;
use Modules\Customer\Http\Controllers\Admin\CustomerController;
use Modules\Customer\Http\Controllers\Admin\ValidCustomerController;
use Modules\Customer\Http\Controllers\Admin\WithdrawController;
use Modules\Customer\Http\Controllers\Customer\ProfileController;

Route::webSuperGroup('admin', function () {

	Route::prefix('/valid-customers')->name('valid-customers.')->group(function () {
		Route::get('/', [ValidCustomerController::class, 'index'])->name('index');
		Route::get('/create', [ValidCustomerController::class, 'create'])->name('create');
		Route::post('/', [ValidCustomerController::class, 'store'])->name('store');
		Route::get('/{valid_customers}/edit', [ValidCustomerController::class, 'edit'])->name('edit');
		Route::put('/{valid_customers}', [ValidCustomerController::class, 'update'])->name('update');
		Route::delete('/{valid_customers}', [ValidCustomerController::class, 'destroy'])->name('destroy');
	});

	Route::prefix('/withdraws')->name('withdraws.')->group(function () {
		Route::get('/', [WithdrawController::class, 'index'])->name('index');
		Route::put('/{withdraw}', [WithdrawController::class, 'update'])->name('update');
	});

	Route::get('/transactions', [CustomerController::class, 'transactionsWallet'])
  ->name('transactions.index')
  ->middleware('permission:read_transaction');

	Route::prefix('/customers')->name('customers.')->group(function () {
    Route::post('customer/withdraw', [CustomerController::class, 'withdrawCustomerWallet'])->name('withdraw');
    Route::post('customer/deposit', [CustomerController::class, 'depositCustomerWallet'])->name('deposit');

    Route::get('/search', [CustomerController::class, 'search'])->name('search');

    Route::get('/', [CustomerController::class,'index'])->name('index');
    Route::get('/create', [CustomerController::class,'create'])->name('create');
    Route::post('/create', [CustomerController::class,'store'])->name('store');
    Route::get('/{customer}', [CustomerController::class,'show'])->name('show');
    Route::get('/{customer}/edit', [CustomerController::class,'edit'])->name('edit');
    Route::put('/{id}', [CustomerController::class,'update'])->name('update');
    Route::delete('/delete/{id}', [CustomerController::class,'destroy'])->name('destroy');
	});
  Route::resource('addresses', 'AddressController')->only(['store', 'update']);
  Route::delete('/addresses/delete/{customer_id}/{address_id}', [AddressController::class, 'destroy'])->name('addresses.destroy');
  Route::get('/get-cities', [AddressController::class, 'getCities'])->name('getCity');
});

Route::webSuperGroup('customer', function () {
  //profile
  Route::get('/get-balance', [ProfileController::class, 'walletBalance'])->name('profile.balance');
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::put('/profile/image', [ProfileController::class, 'uploadImage'])->name('profile.uploadImage');
  //password
  Route::put('/password', [ProfileController::class, 'changePassword'])->name('password');
  //address
  Route::apiResource('addresses', 'AddressController')->only(['index','store', 'update', 'destroy']);
  //wallet
  Route::post('/deposit', [ProfileController::class, 'depositWallet'])->name('profile.deposit');
  Route::apiResource('/withdraws', 'WithdrawController')->only(['index', 'store']);
  Route::post('/withdraws/{withdraw}/cancel', [WithdrawController::class, 'cancel'])->name('withdraws.cancel');
  Route::post('/transactions', [ProfileController::class, 'transactionsWallet'])->name('profile.transactionsWallet');
});
