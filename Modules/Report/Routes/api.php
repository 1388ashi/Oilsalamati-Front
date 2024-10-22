<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Modules\Core\Helpers\Helpers;
use Modules\CustomersClub\Entities\CustomersClubScore;
use Modules\Report\Http\Controllers\Admin\ReportController;
use Modules\Report\Http\Controllers\Admin\WalletDailyBalanceController;

require base_path('vendor/shetabit/shopit/src/Modules/Report/Routes/api.php');


Route::superGroup('admin' ,function () {
    Route::get('reports/customers/incomes-detail', 'ReportController@customersIncomesDetail')->name('reports.customersIncomesDetail');


    Route::get('reports/customers/get_total_income', [ReportController::class, 'getTotalIncome'])->name('reports.getTotalIncome');
    Route::get('reports/customers/get_total_shipping_amount', [ReportController::class, 'getTotalShippingAmount'])->name('reports.getTotalShipping');
    Route::get('reports/customers/get_total_orders', [ReportController::class, 'getTotalOrders'])->name('reports.getTotalOrders');
    Route::get('reports/customers/get_total_order_items', [ReportController::class, 'getTotalOrderItems'])->name('reports.getTotalOrderItems');
    Route::get('reports/customers/get_total_discount_amount', [ReportController::class, 'getTotalDiscountAmount'])->name('reports.getTotalDiscountAmount');
    Route::get('reports/customers/get_total_discount_with_coupon', [ReportController::class, 'getTotalDiscountAmountWithCoupon'])->name('reports.getTotalDiscountWithCoupon');
    Route::get('reports/customers/get_total_discount_without_coupon', [ReportController::class, 'getTotalDiscountAmountWithoutCoupon'])->name('reports.getTotalDiscountWithoutCoupon');
    Route::get('reports/customers/get_total_gift_wallet_amount', [ReportController::class, 'getTotalGiftWalletAmount'])->name('reports.getTotalGiftWalletAmount');

    Route::get('reports/customers/get_total_report', [ReportController::class, 'getTotalReport'])->name('reports.getTotalReport');
    Route::get('reports/customers/get_total_report_list', [ReportController::class, 'getTotalReportList'])->name('reports.getTotalReportList');
    Route::get('reports/customers/get_transaction_deposit', [ReportController::class, 'getTransactionDeposit'])->name('reports.getTransactionDeposit');


    Route::get('reports/chart1_light', [ReportController::class,'chartType1Light'])->name('reports.chart1Light');

    Route::get('reports/varieties_list_light', [ReportController::class, 'varietiesListLight'])->name('reports.varieties-list-light');

//    Route::get('reports/get_daily_balance', [WalletDailyBalanceController::class, 'getWalletBalance'])->name('reports.gat-wallet-daily-balance');
    Route::get('reports/get_daily_balance_list', [WalletDailyBalanceController::class, 'getWalletDailyBalanceList'])->name('reports.gat-wallet-daily-balance-list');

}, ['auth:admin-api', 'permission:report,admin-api']);

Route::get('testCode',function(){
    $amount = 1550000;
    $score = \Modules\CustomersClub\Entities\CustomersClubSellScore::query()
        ->where('min_value','<',$amount)
        ->where('max_value','>',$amount)
        ->first();

    // امتیاز دریافت شده در مرحله خرید محاسبه شده و برای مشتری ثبت می گردد
    $customer_club_score = new CustomersClubScore();
    $customer_club_score->customer_id = 54969;
    $customer_club_score->cause_id = null;
    $customer_club_score->cause_title = (new Modules\Core\Helpers\Helpers)->generateCauseTitleBySellScoreId($score->id, 3295);
    $customer_club_score->score_value = $score->score_value;
    $customer_club_score->bon_value = $score->bon_value;
    $customer_club_score->date = date('Y-m-d');
    $customer_club_score->status = 1;

    $customer_club_score->save();

    dd($score->title);
});


Route::get('report_customer',[ReportController::class,'publicReportCustomer']);
Route::get('report_variety',[ReportController::class,'publicReportVariety']);
Route::get('report_full',[ReportController::class,'publicReportFull']);
Route::get('report_wallet_transaction',[ReportController::class,'publicWalletTransaction']);
//Route::get('checkDate',[ReportController::class,'checkDate']);

Route::get('checkWallet',[ReportController::class,'checkWallet']);
Route::get('checkDifference',[ReportController::class,'checkDifference']);
Route::get('checkWrongWallet',[ReportController::class,'checkWrongWallet']);
Route::get('checkGiftWallet',[ReportController::class,'checkGiftWallet']);
Route::get('checkAllDuplicates',[ReportController::class,'checkAllDuplicates']);

Route::get('checkProductStoreBalance',[ReportController::class,'checkProductStoreBalance']);

Route::get('updateBirthDate',[ReportController::class,'updateBirthDate']);
Route::get('saveMiladiBirthDate',[ReportController::class,'saveMiladiBirthDate']);

Route::get('get-customer-transaction',[ReportController::class,'getCustomerTransactions']);
Route::get('change-transaction-status',[ReportController::class,'changeTransactionsStatus']);
Route::get('update-wallet',[ReportController::class,'updateWallet']);
Route::get('update-gift-wallet',[ReportController::class,'updateGiftWallet']);

Route::get('fullCustomerReport',[ReportController::class,'fullCustomerReport']);





// came from vendor ================================================================================================
Route::superGroup('admin' ,function () {
    Route::get('reports/chart1', 'ReportController@chartType1')
        ->name('reports.chart1');
}, ['auth:admin-api', 'permission:report|mini_order|read_mini_order,admin-api']);

Route::superGroup('admin' ,function () {
    Route::get('reports/chart2', 'ReportController@chartType1')->name('reports.chart2');
    Route::get('reports/varieties', 'ReportController@varieties')->name('reports.varieties');
    Route::get('reports/products', 'ReportController@products')->name('reports.products');
    Route::get('reports/varieties-list', 'ReportController@varietiesList')->name('reports.varieties-list');
    Route::get('reports/customers', 'ReportController@customers')->name('reports.customers');
    Route::get('reports/orders', 'ReportController@orders')->name('reports.orders');
    Route::get('reports/stores', 'ReportController@stores')->name('reports.stores');
    Route::get('reports/wallets', 'ReportController@wallets')->name('reports.wallets');
    Route::get('reports/wallets-balance', 'ReportController@walletsBalance')->name('reports.walletsBalance');
    Route::get('reports/pretty_orders', 'ReportController@prettyOrders')->name('reports.prettyOrder');
    Route::get('reports/order_filter_helper', 'ReportController@orderFilterHelper')->name('reports.orderFilterHelper');
}, ['auth:admin-api', 'permission:report,admin-api']);
