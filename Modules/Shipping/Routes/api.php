<?php

Route::superGroup('admin', function () {
    Route::name('city.assign')->post('shippings/{shipping}/assign-cities','ShippingController@assignCities');
    Route::post('shippings/sort','ShippingController@sort')->hasPermission('modify_shipping');
    Route::permissionResource('shippings','ShippingController');
    Route::permissionResource('shipping_ranges','ShippingRangeController', ['permission_name' => 'shipping']);
});

Route::superGroup('customer', function () {
    Route::get('shippings','ShippingController@index');
});
