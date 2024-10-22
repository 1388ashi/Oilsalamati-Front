<?php

use Illuminate\Support\Facades\Route;
use Modules\Advertise\Http\Controllers\Admin\AdvertiseController;
use Modules\Advertise\Http\Controllers\Admin\PositionAdvertiseController;

Route::webSuperGroup('admin', function () {

  Route::patch('positions/{position}/update_possibility', [AdvertiseController::class,'updatePossibility'])
      ->name('advertisements.update_possibility');
  Route::get('positions/{position}/update_possibility', [AdvertiseController::class,'editPossibility'])
      ->name('advertisements.edit_possibility');

      Route::resource('advertise',  'AdvertiseController');
    //   Route::post('/advertise', [AdvertiseController::class,'store'])->name('advertise.store');

      Route::resource('positions', 'PositionAdvertiseController');

});
