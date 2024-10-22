<?php

//require base_path('vendor/shetabit/shopit/src/Modules/Setting/Routes/api.php');
use Illuminate\Support\Facades\Route;





Route::as('admin.')->namespace('Admin')
    ->prefix('admin')->middleware(['auth:admin-api'])->group(function() {
        Route::delete('/settings/{setting}/file', 'SettingController@destroyFile')
            ->name('settings.destroy-file');
        Route::get('/settings', 'SettingController@index')->name('settings.index');
        Route::get('settings/{group_name}', 'SettingController@show')->name('settings.show');
        Route::put('/settings', 'SettingController@update')->name('settings.update');

        \Illuminate\Support\Facades\Route::get('htaccess', 'HtaccessController@index')
            ->hasPermission('htaccess');
        \Illuminate\Support\Facades\Route::post('htaccess', 'HtaccessController@update')
            ->hasPermission('htaccess');
    });

Route::name('all.')->prefix('all')->group(function () {
    Route::get('settings', 'SettingController@index');
    Route::get('settings/{group_name}', 'SettingController@show');
});

