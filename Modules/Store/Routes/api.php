<?php

Route::superGroup('admin', function() {
    Route::name('store_transactions')
        ->get('store_transactions' , 'StoreTransactionController@index')
    ->hasPermission('read_store');
    Route::permissionResource('stores' , 'StoreController', ['only' => ['index', 'show', 'store']]);
});
