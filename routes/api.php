<?php

use App\Http\Controllers\TagController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('todo', TodoController::class);
Route::post('/getlistOfTodosPagignated', [TodoController::class, 'getlistOfTodosPagignated']);
Route::post('/updateTodo', [TodoController::class, 'updateTodo']);
Route::post('/testing', [TodoController::class, 'testing']);



Route::post('loginOrSignUp', [UserController::class, 'loginOrSignUp'])->name('loginOrSignUp');
Route::post('forgetPassword', [UserController::class, 'forgetPassword'])->name('forgetPassword');
Route::post('verifyPasswordOtp', [UserController::class, 'verifyPasswordOtp'])->name('verifyPasswordOtp');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('createOtp', [UserController::class, 'createOtp'])->name('createOtp');
    Route::post('verifyOtp', [UserController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('updateUserDetails', [UserController::class, 'updateUserDetails'])->name('updateUserDetails');
    Route::post('getUserDetail', [UserController::class, 'getUserDetail'])->name('getUserDetail');
    Route::post('updatePassword', [UserController::class, 'updatePassword'])->name('updatePassword');

    Route::prefix('v1/tags')->name('tags.')->group(function () {
        
        Route::get('/getAllSeeded', [TagController::class, 'getAllSeededTags'])->name('getAllSeeded');
        Route::post('/', [TagController::class, 'getAllTags'])->name('getAll');
        Route::post('/createTag', [TagController::class, 'createTag'])->name('create');
        Route::put('/', [TagController::class, 'updateTag'])->name('update');
        Route::delete('/', [TagController::class, 'deleteTag'])->name('delete');
        
        Route::post('/bulk', [TagController::class, 'bulkCreateTags'])->name('bulkCreate');
        Route::delete('/bulk', [TagController::class, 'bulkDeleteTags'])->name('bulkDelete');
        
        Route::post('/{tag}/archive', [TagController::class, 'archiveTag'])->name('archive');
        Route::post('/{tag}/restore', [TagController::class, 'restoreTag'])->name('restore');
        Route::post('/search', [TagController::class, 'searchTags'])->name('search');
    });
});


Route::post('signOut', [UserController::class, 'signOut'])->name('signOut');
