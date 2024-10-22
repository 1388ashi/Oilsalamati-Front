<?php
use Illuminate\Support\Facades\Route;
use Modules\FAQ\Http\Controllers\Admin\FAQController;

Route::webSuperGroup('admin', function () {
  Route::patch('/faqs/sort', [FAQController::class, 'sort'])->name('faqs.sort');
    Route::get('/faqs', [FAQController::class,'index'])->name('faqs.index');
    Route::post('/faqs', [FAQController::class,'store'])->name('faqs.store');
    Route::patch('/faqs/{id}', [FAQController::class,'update'])->name('faqs.update');
    Route::delete('/faqs/delete/{id}', [FAQController::class,'destroy'])->name('faqs.destroy');

});

