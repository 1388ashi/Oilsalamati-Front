<?php

use Illuminate\Http\Request;
use Modules\CustomersClub\Http\Controllers\Admin\CustomersClubController as CustomersClubControllerAdmin;
use Modules\CustomersClub\Http\Controllers\Customer\CustomersClubController as CustomersClubControllerCustomer;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/customersclub', function (Request $request) {
    return $request->user();
});

Route::superGroup('admin' ,function () {
    Route::group(['prefix' => 'customers_club'] , function (){
        Route::post('get_bought_products', [CustomersClubControllerAdmin::class, 'getBoughtProducts'])->name('customersClub.getBoughtProducts');
        Route::post('set_before_after_images', [CustomersClubControllerAdmin::class, 'setBeforeAfterImage'])->name('customersClub.setBeforeAfterImages');
        Route::post('get_before_after_images', [CustomersClubControllerAdmin::class, 'getBeforeAfterImage'])->name('customersClub.getBeforeAfterImages');
        Route::post('approve_before_after_images', [CustomersClubControllerAdmin::class, 'approveBeforeAfterImage'])->name('customersClub.approveBeforeAfterImages');
        Route::post('delete_before_after_images', [CustomersClubControllerAdmin::class, 'deleteBeforeAfterImage'])->name('customersClub.deleteBeforeAfterImages');

        Route::post('set_story_mention', [CustomersClubControllerAdmin::class, 'setStoryMention'])->name('customersClub.setStoryMention');

        Route::post('set_enamad_score', [CustomersClubControllerAdmin::class, 'setEnamadScore'])->name('customersClub.setEnamadScore');

        Route::get('get_club_score_list', [CustomersClubControllerAdmin::class, 'getClubScoreList'])->name('customersClub.getClubScoreList');

        Route::get('level_users', [CustomersClubControllerAdmin::class, 'getLevelUsers'])->name('customersClub.getLevelUsers');

        // Management

        // امتیازات باشگاه
        Route::get('get_club_scores', [CustomersClubControllerAdmin::class, 'getClubScores'])->name('customersClub.getClubScores');
        Route::put('set_club_scores', [CustomersClubControllerAdmin::class, 'setClubScores'])->name('customersClub.setClubScores');

        // سطح مشتریان
        Route::get('get_user_levels', [CustomersClubControllerAdmin::class, 'getUserLevels'])->name('customersClub.getUserLevels');
        Route::put('set_user_levels', [CustomersClubControllerAdmin::class, 'setUserLevels'])->name('customersClub.setUserLevels');

        // امتیازات خرید
        Route::get('get_purchase_scores', [CustomersClubControllerAdmin::class, 'getPurchaseScores'])->name('customersClub.getPurchaseScores');
        Route::put('set_purchase_score', [CustomersClubControllerAdmin::class, 'setPurchaseScore'])->name('customersClub.setPurchaseScore');
        Route::post('add_purchase_score', [CustomersClubControllerAdmin::class, 'addPurchaseScore'])->name('customersClub.addPurchaseScore');
        Route::delete('delete_purchase_score', [CustomersClubControllerAdmin::class, 'deletePurchaseScore'])->name('customersClub.deletePurchaseScore');

        // حداقل مبلغ اولین خرید کاربر جهت ثبت امتیاز برای معرفی کننده
        Route::post('set_min_first_order', [CustomersClubControllerAdmin::class, 'setMinFirstOrder'])->name('customersClub.setMinFirstOrder');

        // تعیین حداقل زمان موردنیاز برای ثبت استوری
        Route::post('set_min_story_hours', [CustomersClubControllerAdmin::class, 'setMinStoryHours'])->name('customersClub.setMinStoryHours');

        // مبلغ بن به تومان
        Route::get('get_bon_values', [CustomersClubControllerAdmin::class, 'getBonValues'])->name('customersClub.getBonValues');
        Route::post('add_bon_value', [CustomersClubControllerAdmin::class, 'addBonValue'])->name('customersClub.addBonValue');

        // درخواست های تبدیل بن به هدیه کیف پول
        Route::get('get_bon_convert_request_admin_list', [CustomersClubControllerAdmin::class, 'getBonConvertRequestAdminList'])->name('customersClub.getBonConvertRequestAdminList');
        Route::post('update_bon_convert_request', [CustomersClubControllerAdmin::class, 'updateBonConvertRequest'])->name('customersClub.updateBonConvertRequest');

        // تنظیمات تخفیف تولد
        Route::get('get_birth_date_settings', [CustomersClubControllerAdmin::class, 'getBirthdateSettings'])->name('customersClub.getBirthdateSettings');
        Route::post('set_birth_date_settings', [CustomersClubControllerAdmin::class, 'setBirthdateSettings'])->name('customersClub.setBirthdateSettings');
    });
});

//Route::get('test_discount', [CustomersClubControllerAdmin::class, 'testDiscount']);
//Route::get('test_duplicate_score', [CustomersClubControllerAdmin::class, 'testDuplicateScore']);

Route::superGroup('customer', function () {
    Route::group(['prefix' => 'customers_club'] , function (){
        Route::post('get_bought_products', [CustomersClubControllerCustomer::class, 'getBoughtProducts'])->name('customersClub.getBoughtProducts.customer');
        Route::post('set_before_after_images', [CustomersClubControllerCustomer::class, 'setBeforeAfterImage'])->name('customersClub.setBeforeAfterImages.customer');

        Route::get('get_club_score_list', [CustomersClubControllerCustomer::class, 'getClubScoreList'])->name('customersClub.getClubScoreList');
        Route::get('get_club_data', [CustomersClubControllerCustomer::class, 'getClubData'])->name('customersClub.getClubData');

        // درخواست تبدیل بن به هدیه کیف پول
        Route::post('send_bon_convert_request', [CustomersClubControllerCustomer::class, 'sendBonConvertRequest'])->name('customersClub.sendBonConvertRequest'); // ارسال درخواست
        Route::get('get_bon_convert_request_list', [CustomersClubControllerCustomer::class, 'getBonConvertRequestList'])->name('customersClub.getBonConvertRequestList'); // لیست درخواست ها

        Route::get('get_missions', [CustomersClubControllerCustomer::class, 'getMissions'])->name('customersClub.getMissions'); // لیست مأموریت های کاربر
    });
});

Route::group(['prefix' => 'customers_club'] , function () {
    Route::get('get_top_ten', [CustomersClubControllerCustomer::class, 'getTopTen'])->name('customersClub.getTopTen'); // لیست 10 نفر برتر امتیازات ماهانه، سالانه و کل
});
