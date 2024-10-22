<?php

Route::superGroup('admin' ,function () {
    Route::post('category/sort' , 'CategoryController@sort');
    Route::permissionResource('categories', CategoryController::class);
});

Route::superGroup('front' ,function () {
    route::get('special-categories',[\Modules\Category\Http\Controllers\Front\CategoryController::class,'special'])->name('special-categories');
    Route::apiResource('categories', CategoryController::class)->only('index', 'show');
},[]);
