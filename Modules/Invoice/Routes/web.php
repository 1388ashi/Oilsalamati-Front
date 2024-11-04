<?php

use Modules\Invoice\Http\Controllers\All\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('callback', function () {
//    return view('invoice::callback');
//});

//Route::get('pay/request', function (\Modules\Invoice\Drivers\SadadDriver $driver) {
//    $output = $driver->make(100, url('pay/callback'));
//    dd($output);
//});
//Route::get('pay/callback', function (\Illuminate\Http\Request $request) {
//    dd($request->all());
//});
\Illuminate\Support\Facades\Route::name('payment.verify')
    ->any('payment/{gateway}/verify', [PaymentController::class, 'verify'])->name('web.payment.verify');
