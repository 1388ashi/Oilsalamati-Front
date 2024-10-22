<?php

use Illuminate\Support\Facades\Route;
use Modules\Comment\Http\Controllers\Admin\CommentController;

Route::webSuperGroup('admin', function () {
  Route::prefix('/post-comments')->name('post-comments.')->group(function() {
    Route::post('/{comment}/answer', [CommentController::class, 'answer'])->name('answer')->middleware('permission:write_comment');
    Route::get('/{post}', [CommentController::class, 'index'])->name('index')->middleware('permission:read_comment');
    Route::get('/', [CommentController::class, 'all'])->name('all')->middleware('permission:read_comment');
    Route::get('/{comment}/show', [CommentController::class, 'show'])->name('show')->middleware('permission:read_comment');
    Route::put('/{comment}', [CommentController::class, 'update'])->name('update')->middleware('permission:modify_comment');
    Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy')->middleware('permission:delete_comment');
  });
});
