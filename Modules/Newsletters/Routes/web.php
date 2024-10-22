<?php
use Illuminate\Support\Facades\Route;
use Modules\Newsletters\Http\Controllers\Admin\NewslettersController;
use Modules\Newsletters\Http\Controllers\Admin\UsersNewslettersController;

Route::webSuperGroup('admin', function () {
    Route::get('/newsletters', [NewslettersController::class,'index'])->name('newsletters.index');
    Route::get('/newsletters/create', [NewslettersController::class,'create'])->name('newsletters.create');
    Route::post('/newsletters', [NewslettersController::class,'send'])->name('newsletters.store');
    Route::delete('/newsletters/delete/{id}', [NewslettersController::class,'destroy'])->name('newsletters.destroy');

    Route::get('/newsletters/users', [UsersNewslettersController::class,'index'])->name('newsletters.users.index');
    Route::delete('/newsletters/delete/{users_newsletters}', [UsersNewslettersController::class,'destroy'])->name('newsletters.users.destroy');
});

