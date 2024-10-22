<?php
use Illuminate\Support\Facades\Route;
use Modules\CustomersClub\Http\Controllers\Admin\CustomersClubController;

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
// Route::prefix('customersclub')->group(function() {
//     Route::get('/', 'CustomersClubController@index');
// });
Route::webSuperGroup('admin' ,function () {
    Route::group(['prefix' => 'customers_club'] , function (){
        Route::post('get_bought_products', [CustomersClubController::class, 'getBoughtProducts'])->name('customersClub.getBoughtProducts');
        Route::post('set_before_after_images', [CustomersClubController::class, 'setBeforeAfterImage'])->name('customersClub.setBeforeAfterImages');
        Route::get('get_before_after_images', [CustomersClubController::class, 'getBeforeAfterImage'])->name('customersClub.getBeforeAfterImages');
        Route::post('approve_before_after_images', [CustomersClubController::class, 'approveBeforeAfterImage'])->name('customersClub.approveBeforeAfterImages');
        Route::post('delete_before_after_images', [CustomersClubController::class, 'deleteBeforeAfterImage'])->name('customersClub.deleteBeforeAfterImages');

        Route::post('set_story_mention', [CustomersClubController::class, 'setStoryMention'])->name('customersClub.setStoryMention');

        Route::get('page_enamad_score', [CustomersClubController::class, 'pageEnamadScore'])->name('customersClub.pageEnamadScore');
        Route::post('set_enamad_score', [CustomersClubController::class, 'setEnamadScore'])->name('customersClub.setEnamadScore');

        Route::get('get_club_score_list', [CustomersClubController::class, 'getClubScoreList'])->name('customersClub.getClubScoreList');

        Route::get('level_users', [CustomersClubController::class, 'getLevelUsers'])->name('customersClub.getLevelUsers');

        // Management

        // امتیازات باشگاه
        Route::get('get_club_scores', [CustomersClubController::class, 'getClubScores'])->name('customersClub.getClubScores');
        Route::put('set_club_scores', [CustomersClubController::class, 'setClubScores'])->name('customersClub.setClubScores');

        // سطح مشتریان
        Route::get('get_user_levels', [CustomersClubController::class, 'getUserLevels'])->name('customersClub.getUserLevels');
        Route::put('set_user_levels', [CustomersClubController::class, 'setUserLevels'])->name('customersClub.setUserLevels');

        // امتیازات خرید
        Route::get('get_purchase_scores', [CustomersClubController::class, 'getPurchaseScores'])->name('customersClub.getPurchaseScores');
        Route::put('set_purchase_score', [CustomersClubController::class, 'setPurchaseScore'])->name('customersClub.setPurchaseScore');
        Route::post('add_purchase_score', [CustomersClubController::class, 'addPurchaseScore'])->name('customersClub.addPurchaseScore');
        // Route::delete('delete_purchase_score', [CustomersClubController::class, 'deletePurchaseScore'])->name('customersClub.deletePurchaseScore');
        Route::delete('delete_purchase_score/{id}', [CustomersClubController::class, 'deletePurchaseScore'])->name('customersClub.deletePurchaseScore');

        // حداقل مبلغ اولین خرید کاربر جهت ثبت امتیاز برای معرفی کننده
        Route::post('set_min_first_order', [CustomersClubController::class, 'setMinFirstOrder'])->name('customersClub.setMinFirstOrder');

        // تعیین حداقل زمان موردنیاز برای ثبت استوری
        Route::get('page_min_story_hours', [CustomersClubController::class, 'pageMinStoryHours'])->name('customersClub.pageMinStoryHours');
        Route::post('set_min_story_hours', [CustomersClubController::class, 'setMinStoryHours'])->name('customersClub.setMinStoryHours');

        // مبلغ بن به تومان
        Route::get('get_bon_values', [CustomersClubController::class, 'getBonValues'])->name('customersClub.getBonValues');
        Route::post('add_bon_value', [CustomersClubController::class, 'addBonValue'])->name('customersClub.addBonValue');

        // درخواست های تبدیل بن به هدیه کیف پول
        Route::get('get_bon_convert_request_admin_list', [CustomersClubController::class, 'getBonConvertRequestAdminList'])->name('customersClub.getBonConvertRequestAdminList');
        Route::post('update_bon_convert_request', [CustomersClubController::class, 'updateBonConvertRequest'])->name('customersClub.updateBonConvertRequest');

        // تنظیمات تخفیف تولد
        Route::get('get_birth_date_settings', [CustomersClubController::class, 'getBirthdateSettings'])->name('customersClub.getBirthdateSettings');
        Route::post('set_birth_date_settings', [CustomersClubController::class, 'setBirthdateSettings'])->name('customersClub.setBirthdateSettings');
    });
});
