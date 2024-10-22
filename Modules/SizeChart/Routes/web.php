<?php
use Illuminate\Support\Facades\Route;
use Modules\SizeChart\Http\Controllers\Admin\SizeChartController;
use Modules\SizeChart\Http\Controllers\Admin\SizeChartTypeController;

Route::webSuperGroup('admin', function () {
    Route::get('/size-chart', [SizeChartController::class,'index'])->name('sizechart.index');
    Route::post('/size-chart', [SizeChartController::class,'store'])->name('sizechart.store');
    Route::get('/size-chart/{sizechart}/edit', [SizeChartController::class,'edit'])->name('sizechart.edit');
    Route::patch('/size-chart/{sizechart}', [SizeChartController::class,'update'])->name('sizechart.update');
    Route::delete('/size-chart/delete/{sizechart}', [SizeChartController::class,'destroy'])->name('sizechart.destroy');

    Route::get('/size-chart-type', [SizeChartTypeController::class,'index'])->name('sizecharttype.index');
    Route::post('/size-chart-type', [SizeChartTypeController::class,'store'])->name('sizecharttype.store');
    Route::get('/size-chart-type/{sizechart}/edit', [SizeChartTypeController::class,'edit'])->name('sizecharttype.edit');
    Route::patch('/size-chart-type/{sizechart}', [SizeChartTypeController::class,'update'])->name('sizecharttype.update');
    Route::delete('/size-chart-type/delete/{sizechart}', [SizeChartTypeController::class,'destroy'])->name('sizecharttype.destroy');
});
