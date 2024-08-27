<?php

use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('loginOrSignUp', [UserController::class, 'loginOrSignUp'])->name('loginOrSignUp');
Route::post('forgetPassword', [UserController::class, 'forgetPassword'])->name('forgetPassword');
Route::post('verifyPasswordOtp', [UserController::class, 'verifyPasswordOtp'])->name('verifyPasswordOtp');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('createOtp', [UserController::class, 'createOtp'])->name('createOtp');
    Route::post('verifyOtp', [UserController::class, 'verifyOtp'])->name('verifyOtp');
    Route::post('updateUserDetails', [UserController::class, 'updateUserDetails'])->name('updateUserDetails');
    Route::post('getUserDetail', [UserController::class, 'getUserDetail'])->name('getUserDetail');
    Route::post('signOut', [UserController::class, 'signOut'])->name('signOut');
    Route::post('updatePassword', [UserController::class, 'updatePassword'])->name('updatePassword');

    Route::prefix('v1/tags')->name('tags.')->group(function () {

        Route::get('/getAllSeeded', [TagController::class, 'getAllSeededTags'])->name('getAllSeeded');
        Route::post('/getAllTagsByUserId', [TagController::class, 'getAllTagsByUserId'])->name('getAllTagsByUserId');
        Route::post('/', [TagController::class, 'getAllTags'])->name('getAll');
        Route::post('/createTag', [TagController::class, 'createTag'])->name('create');
        Route::put('/', [TagController::class, 'updateTag'])->name('update');
        Route::post('/getTagDetail', [TagController::class, 'getTagByTagId'])->name('update');
        Route::delete('/', [TagController::class, 'deleteTag'])->name('delete');

        Route::post('/bulk', [TagController::class, 'bulkCreateTags'])->name('bulkCreate');
        Route::post('/bulkDelete', [TagController::class, 'bulkDeleteTags'])->name('bulkDelete');

        Route::post('/{tag}/archive', [TagController::class, 'archiveTag'])->name('archive');
        Route::post('/{tag}/restore', [TagController::class, 'restoreTag'])->name('restore');
        Route::post('/bulkDeleteTagsByTodoId', [TagController::class, 'bulkDeleteTagsByTodoId'])->name('bulkDeleteTagsByTodoId');

        Route::post('/search', [TagController::class, 'searchTags'])->name('search');
    });

    Route::prefix('todo')->name('todo.')->group(function () {
        Route::get('/', [TodoController::class, 'index'])->name('index');
        Route::post('/store', [TodoController::class, 'store'])->name('store');
        Route::post('/deleteTodo', [TodoController::class, 'deleteTodo'])->name('deleteTodo');  // Delete Todo route
        Route::post('/updateTodo', [TodoController::class, 'updateTodo']);
        Route::post('/{todo}', [TodoController::class, 'update'])->name('update');
        Route::post('/getlistOfTodosPagignated', [TodoController::class, 'getlistOfTodosPagignated'])->name('getlistOfTodosPagignated');
        Route::post('/{todo}', [TodoController::class, 'show'])->name('show');
        Route::post('/{todo}/edit', [TodoController::class, 'edit'])->name('edit');
        Route::post('/{todo}/create', [TodoController::class, 'create'])->name('create');
    });

    
    Route::prefix('pomodoro')->name('pomodoro.')->group(function () {
        
        Route::post('/createBulkPomodoros', [PomodoroController::class, 'createBulkPomodoros'])->name('createBulkPomodoros');
        Route::post('/startPomodoro', [PomodoroController::class, 'startPomodoro'])->name('startPomodoro');
        Route::post('/stopPomodoro', [PomodoroController::class, 'stopPomodoro'])->name('stopPomodoro');
        Route::post('/resumePomodoro', [PomodoroController::class, 'resumePomodoro'])->name('resumePomodoro');
        Route::post('/endPomodoro', [PomodoroController::class, 'endPomodoro'])->name('endPomodoro');
        Route::post('/getPomodoroStats', [PomodoroController::class, 'getPomodoroStats'])->name('getPomodoroStats');
        
    });

});

