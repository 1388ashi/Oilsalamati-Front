<?php

 use \Modules\Contact\Http\Controllers\Admin\ContactController as AdminContactController;
 use Modules\Contact\Http\Controllers\Customer\ContactController as CustomerContactController;
// use \Shetabit\Shopit\Modules\Contact\Http\Controllers\All\ContactController as AllContactController;

 Route::superGroup('admin', function () {
     Route::post('contacts/{contact}/read', 'ContactController@read')->hasPermission('contact_modify');
     Route::permissionResource('contacts', 'ContactController', ['only' => ['index', 'show', 'destroy']]);
     Route::put('contacts/{contact}/answer', [AdminContactController::class,'answer']);
 });

 Route::superGroup('customer', function () {
     Route::post('contacts', [CustomerContactController::class,'store'])->name('contacts.store');
     Route::get('contacts', [CustomerContactController::class,'index']);
     Route::get('contacts/{contact}', [CustomerContactController::class,'show']);

 });

 Route::superGroup('all', function () {
     // Route::resource('contacts', 'ContactController')->only(['store']);
     Route::get('contacts/create', 'ContactController@create');
 }, []);

