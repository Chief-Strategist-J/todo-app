<?php

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

Route::post('loginOrSignUp', [UserController::class, 'loginOrSignUp'])->name('loginOrSignUp');

Route::post('createOtp', [UserController::class, 'createOtp'])->name('createOtp');
Route::post('verifyOtp', [UserController::class, 'verifyOtp'])->name('verifyOtp');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('registerUserDetails', [UserController::class, 'registerUserDetails'])->name('registerUserDetails');
});



Route::post('forgetPassword', [UserController::class, 'forgetPassword'])->name('forgetPassword');
Route::post('signOut', [UserController::class, 'signOut'])->name('signOut');
