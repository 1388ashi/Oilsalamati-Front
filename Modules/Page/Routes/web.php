<?php
use Illuminate\Support\Facades\Route;
use Modules\Page\Http\Controllers\Admin\PageController;

Route::webSuperGroup('admin', function () {
  Route::get('/pages', [PageController::class,'index'])->name('pages.index');
  Route::get('/pages/create', [PageController::class,'create'])->name('pages.create');
  Route::post('/pages', [PageController::class,'store'])->name('pages.store');
  Route::get('/pages/{page}/edit', [PageController::class,'edit'])->name('pages.edit');
  Route::patch('/pages/{page}/edit', [PageController::class,'update'])->name('pages.update');
  Route::delete('/pages/delete/{page}', [PageController::class,'destroy'])->name('pages.destroy');
});

