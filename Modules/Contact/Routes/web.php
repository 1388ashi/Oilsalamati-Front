<?php
use Illuminate\Support\Facades\Route;
use Modules\Contact\Http\Controllers\Admin\ContactController;

Route::webSuperGroup('admin', function () {
  Route::patch('contacts/read', [ContactController::class,'read'])->name('contacts.read');
  Route::get('/contacts', [ContactController::class,'index'])->name('contacts.index');
  Route::patch('/contacts/answer', [ContactController::class,'answer'])->name('contacts.answer');
  Route::delete('/contacts/delete/{contact}', [ContactController::class,'destroy'])->name('contacts.destroy');
});
Route::prefix('/contacts')->name('contacts.')->group(function() {
  Route::post('/', [Modules\Contact\Http\Controllers\Customer\ContactController::class,'store'])->name('store');
  // Route::get('/contacts', [Modules\Contact\Http\Controllers\Customer\ContactController::class,'index'])->name('index');
  Route::get('/', [Modules\Contact\Http\Controllers\All\ContactController::class,'index'])->name('index');
});
Route::get('/about-us', [Modules\Contact\Http\Controllers\All\ContactController::class,'aboutUs'])->name('about-us');
// Route::get('contacts/{contact}', [CustomerContactController::class,'show']);