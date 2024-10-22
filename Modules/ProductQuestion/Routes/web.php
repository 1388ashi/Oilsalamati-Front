<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductQuestion\Http\Controllers\Admin\ProductQuestionController;

Route::webSuperGroup('admin', function () {
	Route::prefix('/product-questions')->name('product-questions.')->group(function () {
		Route::get('/', [ProductQuestionController::class, 'index'])->name('index');
		Route::get('/{id}', [ProductQuestionController::class, 'show'])->name('show');
        Route::post('/answer', [ProductQuestionController::class, 'answer'])->name('answer');
		Route::delete('/{id}', [ProductQuestionController::class, 'destroy'])->name('destroy');
		Route::patch('/', [ProductQuestionController::class, 'assignStatus'])->name('assign-status');
	});
});