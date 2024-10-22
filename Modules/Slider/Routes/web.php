<?php
use Illuminate\Support\Facades\Route;
use Modules\Slider\Http\Controllers\Admin\SliderController;

Route::webSuperGroup('admin', function () {
  Route::prefix('sliders')->name('sliders.')->group(function () {

    Route::prefix('groups')->name('groups')->group(function () {
      Route::get('/', [SliderController::class, 'groups']);
      Route::get('/{group}', [SliderController::class, 'index'])->name('.index');
    });

    Route::post('/', [SliderController::class, 'store'])->name('store');
    Route::put('/{slider}', [SliderController::class, 'update'])->name('update');
    Route::delete('/{slider}', [SliderController::class, 'destroy'])->name('destroy');
    Route::patch('/sort/{group}', [SliderController::class, 'sort'])->name('sort');
  });
});
