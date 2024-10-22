<?php
use Illuminate\Support\Facades\Route;
use Modules\Contact\Http\Controllers\Admin\ContactController;

Route::webSuperGroup('admin', function () {
  Route::patch('contacts/read', [ContactController::class,'read'])->name('contacts.read');
  Route::get('/contacts', [ContactController::class,'index'])->name('contacts.index');
  Route::patch('/contacts/answer', [ContactController::class,'answer'])->name('contacts.answer');
  Route::delete('/contacts/delete/{contact}', [ContactController::class,'destroy'])->name('contacts.destroy');
});
