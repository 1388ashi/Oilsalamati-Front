<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\Admin\NewReportController;

Route::webSuperGroup('admin' ,function () {
    Route::prefix('/reports')->name('reports.')->group(function () {
        Route::get('/siteviews', [NewReportController::class, 'siteviews'])->name('siteviews');
        Route::get('/load-siteviews', [NewReportController::class, 'loadSiteViews'])->name('load-siteviews');
        Route::get('/customers', [NewReportController::class, 'customers'])->name('customers');
        Route::get('/products', [NewReportController::class, 'products'])->name('products');
        Route::get('/varieties', [NewReportController::class, 'varieties'])->name('varieties');
        Route::get('/varieties-balance', [NewReportController::class, 'varietiesBalance'])->name('varieties-balance');
        Route::get('/load-varieties', [NewReportController::class, 'loadVarieties'])->name('load-varieties');
        Route::get('/wallets', [NewReportController::class, 'wallets'])->name('wallets');
        Route::get('/orders', [NewReportController::class, 'orders'])->name('orders');
    });
});