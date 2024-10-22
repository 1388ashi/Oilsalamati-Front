<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Admin\MenuItemController;

Route::webSuperGroup('admin', function () {
  Route::prefix('menu')->name('menu.')->group(function () {

    Route::get('/groups', [MenuItemController::class, 'groups'])->name('groups');
    Route::get('/{id}', [MenuItemController::class, 'index'])->name('index');
    Route::get('/{id}/{menu_item}', [MenuItemController::class, 'childIndex'])->name('childIndex');
    Route::get('/grandchild/{id}/{menu_item}', [MenuItemController::class, 'grandChildIndex'])->name('grandchildIndex');

    Route::post('/', [MenuItemController::class, 'store'])->name('store');
    Route::put('/{menu_item}', [MenuItemController::class, 'update'])->name('update');
    Route::delete('/{id}', [MenuItemController::class, 'destroy'])->name('destroy');
    Route::patch('/sort/{id}/{menu_item?}', [MenuItemController::class,'sort'])->name('sort');
  });
});
