<?php

use Modules\Home\Http\Controllers\BeforeAfterImageController;
use Illuminate\Support\Facades\Route;
use Modules\Home\Http\Controllers\HomeController;

Route::superGroup('admin' ,function () {
    Route::group(['prefix' => 'home'], function () {
        Route::post('set_before_after_images', [BeforeAfterImageController::class, 'setBeforeAfterImage']);
        Route::post('get_before_after_images', [BeforeAfterImageController::class, 'getBeforeAfterImagesForAdmin']);
        Route::post('get_before_after_image/{id}', [BeforeAfterImageController::class, 'getBeforeAfterImageForAdmin']);
        Route::post('update_before_after_image/{id}', [BeforeAfterImageController::class, 'updateBeforeAfterImageForAdmin']);
        Route::post('change_status_before_after_images', [BeforeAfterImageController::class, 'changeStatusBeforeAfterImage']);
        Route::post('delete_before_after_images', [BeforeAfterImageController::class, 'deleteBeforeAfterImage']);
    });
});

Route::get('front/home' , [HomeController::class, 'index'])->name('front.home');
Route::get('front/base' , [HomeController::class, 'base'])->name('front.base');
Route::get('front/get_user' , [HomeController::class, 'get_user'])->name('front.get_user');

Route::get('front/home/item/{itemName}' , [HomeController::class, 'item'])->name('front.home.item');


//Route::get('front/home_light' , 'HomeController@index_light')->name('front.home.index-light');

//Route::get('front/suggestions' , 'HomeController@getSuggestions')->name('front.suggestions');
//Route::get('front/most-discounts' , 'HomeController@getMostDiscounts')->name('front.most-discounts');
//Route::get('front/recommendations/{group}' , 'HomeController@getRecommendationsByGroup')->name('front.recommendations');
//Route::get('front/most-discounts-new' , 'HomeController@getMostDiscountsNew')->name('front.most-discounts');
//Route::get('front/most-timed-discounts' , 'HomeController@getMostTimedDiscounts')->name('front.most-timed-discount');
//Route::get('front/most-sales' , 'HomeController@getMostSales')->name('front.most-sales');
//Route::get('front/new-products' , 'HomeController@getNewProducts')->name('front.new-products');
//Route::get('front/sliders' , 'HomeController@getSliders')->name('front.sliders');
//Route::get('front/is-package' , 'HomeController@getIsPackage')->name('front.is-package');
//Route::post('front/home/get_before_after_images', [BeforeAfterImageController::class, 'getBeforeAfterImagesForFront'])->name('front.getBeforeAfterImagesForFront');


//Route::get('front/free_shipping_products' , 'HomeController@getFreeShippingProducts')->name('front.free_shipping_products');
