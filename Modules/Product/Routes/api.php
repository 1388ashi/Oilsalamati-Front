<?php

//require base_path('vendor/shetabit/shopit/src/Modules/Product/Routes/api.php');
use Illuminate\Http\Request;
use Modules\Product\Http\Controllers\Admin\ProductSetController;
use Modules\Product\Http\Controllers\Admin\RecommendationController;
use \Modules\Product\Http\Controllers\Admin\CustomRelatedProductController;
use \Modules\Product\Http\Controllers\Admin\OrderProductController;
Route::superGroup("customer" ,function (){
    Route::post('products/{product}/listen-discount', 'ListenDiscountController@store')->name('products.listen-discount');
    Route::delete('products/{product}/unlisten-discount', 'ListenDiscountController@destroy')->name('products.unlisten-discount');
    Route::get('customer-delivered-products',[\Modules\Product\Http\Controllers\Customer\ProductController::class,'getCustomerProducts']);
});


Route::superGroup("admin" ,function (){
    \Illuminate\Support\Facades\Route::name('products.excel')
        ->get('products/{id}/excel', 'ProductController@excel')
        ->hasPermission('read_product');
    Route::name('products.create')->get('products/create', 'ProductController@create');
    Route::name('products.search')->get('products/search', 'ProductController@search');
    Route::permissionResource('products' , ProductController::class);
    Route::post('products/{id}/{type}' , 'ProductController@approved_product')
        ->where('type', 'approve|disapprove')
        ->hasPermission('approved_products');


    Route::get('product_sets', [ProductSetController::class, 'index'])->name('product_sets.index')->hasPermission('read_product');
    Route::get('product_sets/{product_set}', [ProductSetController::class, 'show'])->name('product_sets.show')->hasPermission('read_product');
    Route::post('product_sets', [ProductSetController::class, 'store'])->name('product_sets.store')->hasPermission('write_product');
    Route::put('product_sets/{product_set}', [ProductSetController::class, 'update'])->name('product_sets.update')->hasPermission('modify_product');
    Route::delete('product_sets/{product_set}', [ProductSetController::class, 'destroy'])->name('product_sets.delete')->hasPermission('delete_product');

    Route::get('recommendations/groups/{group}', [RecommendationController::class, 'index'])->name('recommendations.index')->hasPermission('recommendation');
    Route::get('recommendations/create', [RecommendationController::class, 'create'])->name('recommendations.create')->hasPermission('recommendation');
    Route::post('recommendations', [RecommendationController::class, 'store'])->name('recommendations.store')->hasPermission('recommendation');
    Route::put('recommendations/sort_priority', [RecommendationController::class, 'sortPriority'])->name('recommendations.sortPriority')->hasPermission('recommendation');
    Route::post('recommendations/groups/{group}/sort', [RecommendationController::class, 'sort'])->name('recommendations.sort')->hasPermission('recommendation');
    Route::delete('recommendations/{recommendation}', [RecommendationController::class, 'destroy'])->name('recommendations.delete')->hasPermission('recommendation');

    Route::permissionResource('gifts' , GiftController::class, ['permission' => 'product']);

    //product/{id}/related-product    => get product list
    //product/{id}/related-product    => delete a related
    //store => product_id,related_id

    Route::get('product/{id}/related-product', [CustomRelatedProductController::class, 'index'])->name('custom-related-product.index')->hasPermission('custom-related-product');
    Route::delete('product/{id}/related-product', [CustomRelatedProductController::class, 'destroy'])->name('custom-related-product.delete')->hasPermission('custom-related-product');
    Route::post('product/related-product', [CustomRelatedProductController::class, 'store'])->name('custom-related-product.store')->hasPermission('custom-related-product');

    //get product where order is not null
    Route::get('product-order', [OrderProductController::class, 'index'])->name('order-product.index')->hasPermission('order-product');

    ////add product to order list
    Route::post('product-order', [OrderProductController::class, 'store'])->name('order-product.store')->hasPermission('order-product');

    // change orders
    Route::post('product-order/change-order', [OrderProductController::class, 'changeOrder'])->name('product-order.change-order')->hasPermission('order-product');

    // make order null
    Route::post('product-order/{id}/make-order-null', [OrderProductController::class, 'makeOrderIdNull'])->name('order-product.make-order-id-null')->hasPermission('order-product');


    Route::get('category-product-sort-index/{category}/', 'CategoryProductSortController@index')->hasPermission('order-product');
    \Illuminate\Support\Facades\Route::resource('category-product-sort','CategoryProductSortController')->except(['index']);//->hasPermission('order-product');
});



// came from vendor ================================================================================================
use Modules\Product\Http\Controllers\Front\ProductController as AllProductController;
use Modules\Product\Http\Controllers\Customer\ProductController as CustomerProductController;
use Modules\Product\Http\Controllers\Front\CompareController;
use Modules\Product\Http\Controllers\Front\PPCController;

Route::superGroup("front" ,function (){
    Route::get('products/search', [AllProductController::class, 'search'])->name('products.search');
    Route::get('products', [AllProductController::class, 'index'])->name('products.index');
    Route::get('products/compare', [CompareController::class, 'index'])->name('product.compare');
    Route::get('products/compare/search', [CompareController::class, 'search'])->name('product.compare.search');
    Route::get('products/{product}', [AllProductController::class, 'show'])->name('product.show');
},[]);

Route::post('torob/products', [PPCController::class, 'torob']);
Route::get('emalls/products', [PPCController::class, 'emalls']);

Route::superGroup("customer" ,function (){
    Route::post('products/{product}/listen', 'ListenChargeController@store')->name('products.listen');
    Route::delete('products/{product}/unlisten', 'ListenChargeController@destroy')->name('products.unlisten');
    Route::get('favorites', [CustomerProductController::class, 'indexFavorites'])->name('favorites.indexFavorites');
    Route::post('products/{product}/favorite', [CustomerProductController::class, 'addToFavorites'])->name('product.addToFavorites');
    Route::delete('products/{product}/favorite', [CustomerProductController::class, 'deleteFromFavorites'])->name('product.deleteFromFavorites');
});
