<?php
use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\Admin\NotificationController;

Route::webSuperGroup('admin', function () {
    Route::post('notifications/read', 'NotificationController@read')->name('notifications.read');
    Route::get('notifications', 'NotificationController@index')->name('notifications.index');

    Route::get('notifications_public', 'NotificationController@index_public')->name('notifications_public.index');
    Route::get('notifications_public/{id}', 'NotificationController@get_public')->name('notifications_public.get');
    Route::post('notifications_public', 'NotificationController@add_public')->name('notifications_public.add');
    Route::post('notifications_public_update', 'NotificationController@update_public')->name('notifications_public.update');
    Route::delete('notifications_public_delete/{id}', 'NotificationController@delete_public')->name('notifications_public.delete');


    Route::get('notifications_public_for_selected', 'NotificationController@index_public_for_selected')->name('notifications_public.index_for_selected');
    Route::get('notifications_public_for_selected/{id}', 'NotificationController@get_public_for_selected')->name('notifications_public.get_for_selected');
    Route::post('notifications_public_for_selected', 'NotificationController@add_public_for_selected')->name('notifications_public.add_for_selected');
    Route::post('notifications_public_for_selected_update', 'NotificationController@update_public_for_selected')->name('notifications_public.update_for_selected');
    Route::post('notifications_public_for_selected_delete', 'NotificationController@delete_public_for_selected')->name('notifications_public.delete_for_selected');
});
