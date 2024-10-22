<?php
//require base_path('vendor/shetabit/shopit/src/Modules/Setting/Routes/web.php');

// came from vendor ================================================================================================

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\Admin\SettingController;

//Route::group(['prefix' => 'develop', 'namespace' => 'Develop'], function () {
//	Route::resource('settings', SettingController::class);
//});

Route::webSuperGroup('admin', function () {
	Route::prefix('/settings')->name('settings.')->group(function () {
		Route::delete('/{setting}/file', [SettingController::class, 'destroyFile'])->name('destroy-file');
		Route::get('/{group_name}', [SettingController::class, 'show'])->name('show');
		Route::put('/', [SettingController::class, 'update'])->name('update');
	});
});

