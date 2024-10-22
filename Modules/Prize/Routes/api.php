<?php
//require base_path('vendor/shetabit/shopit/src/Modules/Prize/Routes/api.php');

use Illuminate\Support\Facades\Route;

Route::superGroup('admin', function () {
    Route::post('group_charges', 'GroupChargeController@store')->name('group_charges.store');
});
