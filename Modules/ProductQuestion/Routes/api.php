<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::superGroup('admin' ,function () {
    Route::get('product-question', 'ProductQuestionController@index')->hasPermission('read_productQuestion');
    Route::post('product-question', 'ProductQuestionController@assignStatus')->hasPermission('write_productQuestion');
    Route::post('product-question/answer', 'ProductQuestionController@answer')->hasPermission('write_productQuestion');
    Route::delete('product-question/{id}', 'ProductQuestionController@destroy')->hasPermission('delete_productQuestion');
});

Route::superGroup('customer' ,function () {
    Route::apiResource('product-question', 'ProductQuestionController');
});

Route::superGroup('front' ,function () {
    Route::get('product-question/{product_id}', 'ProductQuestionController@show');
}, []);
