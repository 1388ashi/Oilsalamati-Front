<?php
use Illuminate\Support\Facades\Route;
use Modules\Home\Http\Controllers\HomeController;

Route::get('front/home' , [HomeController::class, 'index'])->name('front.home');
Route::get('front/base' , [HomeController::class, 'base'])->name('front.base');
Route::get('front/get_user' , [HomeController::class, 'get_user'])->name('front.get_user');

Route::get('front/home/item/{itemName}' , [HomeController::class, 'item'])->name('front.home.item');
