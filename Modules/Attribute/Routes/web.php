<?php
use Illuminate\Support\Facades\Route;
use Modules\Attribute\Http\Controllers\Admin\AttributeController;

Route::webSuperGroup('admin', function () {
  Route::prefix('attributes')->name('attributes.')->group(function () {
    Route::get('/', [AttributeController::class,'index'])->name('index');
    Route::get('/create', [AttributeController::class,'create'])->name('create');
    Route::get('/edit/{attribute}', [AttributeController::class,'edit'])->name('edit');
    Route::post('/', [AttributeController::class,'store'])->name('store');
    Route::put('/{attribute}', [AttributeController::class,'update'])->name('update');
    Route::delete('/delete/{attribute}', [AttributeController::class,'destroy'])->name('destroy');
  });
});
