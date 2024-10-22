<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Hekmatinasser\Verta\Verta;
use Modules\Order\Jobs\ChangeStatusNotificationJob;
use Modules\Order\Services\Statuses\ChangeStatus;
use Shetabit\Shopit\Modules\Sms\Sms;

Route::get('test', [\App\Http\Controllers\testController::class, 'index']);
Route::get('variety/{type}', [\App\Http\Controllers\testController::class, 'variety']);
Route::get('add', [\App\Http\Controllers\testController::class, 'add']);


//Route::get('log-transfer', [\App\Http\Controllers\Extra\LogActivityController::class, 'logActivity']);
//Route::get('table', [\App\Http\Controllers\testController::class, 'table']);
//Route::get('customer-report', [\App\Http\Controllers\CustomerReportController::class, 'index']);
//Route::get('customer-report/add', [\App\Http\Controllers\CustomerReportController::class, 'add']);
//Route::get('customer-report/get_excel', [\App\Http\Controllers\CustomerReportController::class, 'get_excel']);


//Route::get('customers-club-report', [\App\Http\Controllers\CustomersClubScoresReportController::class, 'index']);
//Route::get('customers-club-report/add', [\App\Http\Controllers\CustomersClubScoresReportController::class, 'add']);
//Route::get('customers-club-report/get_excel', [\App\Http\Controllers\CustomersClubScoresReportController::class, 'get_excel']);
//
//Route::group(['prefix' => 'admin/FileManager', 'middleware' => ['auth:admin']], function () {
//    \UniSharp\LaravelFilemanager\Lfm::routes();
//});


Route::get('transfer_extra/{tableName}', [\App\Http\Controllers\TransferToExtraController::class, 'transfer']);
