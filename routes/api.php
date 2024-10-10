<?php

use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\ProjectCategoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectPhaseController;
use App\Http\Controllers\ProjectPriorityController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\ProjectTypeController;
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
Route::get('getProjectCategoryDetail', [ProjectController::class, 'getProjectCategoryDetail'])->name('getProjectCategoryDetail');

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
        Route::post('/', [TodoController::class, 'getUserRelatedTodo'])->name('getUserRelatedTodo');
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

    Route::prefix('projects')->name('projects.')->group(function () {
        Route::post('/', [ProjectController::class, 'createProject'])->name('create');
        Route::post('/getPaginated', [ProjectController::class, 'getProjects'])->name('getPaginated');
        Route::post('/updateProject', [ProjectController::class, 'updateProject'])->name('update');
        Route::post('/assignTodos', [ProjectController::class, 'assignTodosToProjects'])->name('assignTodos');
        Route::post('/getPaginatedProjectsForTodo', [ProjectController::class, 'getPaginatedProjectsForTodo'])->name('getPaginatedProjectsForTodo');
        Route::post('/getPaginatedTodosForProject', [ProjectController::class, 'getPaginatedTodosForProject'])->name('getPaginatedTodosForProject');
        Route::post('/delete', [ProjectController::class, 'deleteProject'])->name('delete');
        Route::post('/bulkDelete', [ProjectController::class, 'bulkDeleteProjects'])->name('bulkDelete');
        Route::post('/search', [ProjectController::class, 'searchProjects'])->name('search');
        Route::post('/archive', [ProjectController::class, 'archiveProject'])->name('archive');
        Route::post('/restore', [ProjectController::class, 'restoreProject'])->name('restore');
        Route::post('/getProjectForUserCategoryDetail', [ProjectController::class, 'getProjectForUserCategoryDetail'])->name('getProjectForUserCategoryDetail');

    });

    Route::prefix('v1/categories')->name('categories.')->group(function () {
        Route::get('/user', [ProjectCategoryController::class, 'retrieveCategoriesByUser'])->name('retrieveByUser');
        Route::post('/create', [ProjectCategoryController::class, 'createCategoryForUser'])->name('create');
        Route::post('/update', [ProjectCategoryController::class, 'updateCategoryForUser'])->name('update');
        Route::post('/delete', [ProjectCategoryController::class, 'deleteCategoryForUser'])->name('delete'); // Assuming 'id' is passed as a route parameter
    });

    Route::prefix('v1/phases')->name('phases.')->group(function () {
        Route::post('/retrieve', [ProjectPhaseController::class, 'retrievePhasesByUser'])->name('retrieve');
        Route::post('/create', [ProjectPhaseController::class, 'createPhaseForUser'])->name('create');
        Route::post('/update', [ProjectPhaseController::class, 'updatePhaseForUser'])->name('update');
        Route::post('/delete', [ProjectPhaseController::class, 'deletePhaseForUser'])->name('delete');
    });

    Route::prefix('v1/priorities')->name('priorities.')->group(function () {
        Route::post('/retrieve', [ProjectPriorityController::class, 'retrievePrioritiesByUser'])->name('retrieve');
        Route::post('/create', [ProjectPriorityController::class, 'createPriorityForUser'])->name('create');
        Route::post('/update', [ProjectPriorityController::class, 'updatePriorityForUser'])->name('update');
        Route::post('/delete', [ProjectPriorityController::class, 'deletePriorityForUser'])->name('delete');
    });
    
    Route::prefix('v1/statuses')->name('statuses.')->group(function () {
        Route::post('/retrieve', [ProjectStatusController::class, 'retrieveStatusesByUser'])->name('retrieve');
        Route::post('/create', [ProjectStatusController::class, 'createStatusForUser'])->name('create');
        Route::post('/update', [ProjectStatusController::class, 'updateStatusForUser'])->name('update');
        Route::post('/delete', [ProjectStatusController::class, 'deleteStatusForUser'])->name('delete');
    });
    
    Route::prefix('v1/types')->name('types.')->group(function () {
        Route::post('/retrieve', [ProjectTypeController::class, 'retrieveTypesByUser'])->name('retrieve');
        Route::post('/create', [ProjectTypeController::class, 'createTypeForUser'])->name('create');
        Route::post('/update', [ProjectTypeController::class, 'updateTypeForUser'])->name('update');
        Route::post('/delete', [ProjectTypeController::class, 'deleteTypeForUser'])->name('delete');
    });
    

});

