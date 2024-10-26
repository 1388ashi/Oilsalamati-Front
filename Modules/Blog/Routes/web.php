<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\Admin\PostCategoryController;
use Modules\Blog\Http\Controllers\Admin\PostController;
use Modules\Blog\Http\Controllers\Front\PostController as FrontPostController;
use Modules\Blog\Http\Controllers\Front\PostCategoryController as FrontPostCategoryController;

Route::webSuperGroup('admin', function () {

  // Post-Category
  Route::prefix('/post-categories')->name('post-categories.')->group(function () {
    Route::get('/', [PostCategoryController::class, 'index'])->name('index')->middleware('permission:read_post-category');
    Route::post('/', [PostCategoryController::class, 'store'])->name('store')->middleware('permission:write_post-category');
    Route::patch('/{post_category}', [PostCategoryController::class, 'update'])->name('update')->middleware('permission:modify_post-category');
    Route::delete('/{post_category}', [PostCategoryController::class, 'destroy'])->name('destroy')->middleware('permission:delete_post-category');
  });

  // Posts 
  Route::prefix('/posts')->name('posts.')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('index')->middleware('permission:read_post');
    Route::get('/create', [PostController::class, 'create'])->name('create')->middleware('permission:write_post');
    Route::get('/{post}', [PostController::class, 'show'])->name('show')->middleware('permission:read_post');
    Route::post('/', [PostController::class, 'store'])->name('store')->middleware('permission:write_post');
    Route::get('/{post}/edit', [PostController::class, 'edit'])->name('edit')->middleware('permission:modify_post');
    Route::patch('/{post}', [PostController::class, 'update'])->name('update')->middleware('permission:modify_post');
    Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy')->middleware('permission:delete_post');
  });
});
  Route::get('/post-categories', [FrontPostCategoryController::class, 'index'])->name('postCategories');
  Route::get('/posts', [FrontPostController::class, 'index'])->name('posts');
  Route::get('/posts/category/{category_id}', [FrontPostController::class, 'byCategory'])->name('category.posts');
  Route::get('/posts/{id}', [FrontPostController::class, 'show'])->name('posts.show');

