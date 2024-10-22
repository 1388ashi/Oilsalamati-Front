<?php
use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\Admin\RoleController;

Route::webSuperGroup('admin', function () {
    //auth
    Route::get('/roles', [RoleController::class,'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class,'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class,'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class,'edit'])->name('roles.edit');
    Route::patch('/roles/{role}', [RoleController::class,'update'])->name('roles.update');
    Route::delete('/roles/delete/{role}', [RoleController::class,'destroy'])->name('roles.destroy');
});
