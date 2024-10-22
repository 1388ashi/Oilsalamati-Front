<?php
//require base_path('vendor/shetabit/shopit/src/Modules/Customer/Routes/api.php');

use Modules\Customer\Http\Controllers\Admin\ChargeTypeController;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Setting;
use Shetabit\Shopit\Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Sms\Sms;

Route::superGroup('admin' ,function () {
    Route::get('charge_types', [ChargeTypeController::class,'index'])->name('charge_types');

    Route::permissionResource('valid-customers','ValidCustomerController');//->hasPermission('sample');

});
Route::get('front/valid-customers', [\Modules\Customer\Http\Controllers\Front\ValidCustomerController::class,'index'])->name('valid-customers.index');




// came from vendor ================================================================================================

use Modules\Customer\Http\Controllers\Admin\CustomerController;
use Modules\Customer\Http\Controllers\Customer\ProfileController;
use Modules\Customer\Http\Controllers\Customer\WithdrawController;

Route::superGroup('admin', function () {
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customer.search');

    Route::get('customers/transactions', 'CustomerController@transactionsWallet')
        ->name('customers.transactions.index')->hasPermission('read_transaction');

    Route::apiResource('customer_roles', 'CustomerRoleController');
    Route::permissionResource('customers', 'CustomerController');

    Route::post('customer/deposit', [CustomerController::class, 'depositCustomerWallet'])
        ->name('customer.deposit')
        ->hasPermission('modify_customer');

    Route::post('customer/withdraw', [CustomerController::class, 'withdrawCustomerWallet'])
        ->name('customer.withdraw')
        ->hasPermission('modify_customer');

    Route::delete('customers/addresses/{customer}/{address}', 'AddressController@destroy');

    Route::apiResource('customers/addresses', 'AddressController')
        ->only(['store', 'update']);

    Route::permissionResource('withdraws', 'WithdrawController');
});

Route::superGroup('customer', function () {
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
