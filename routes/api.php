<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NewsSourceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    /**
     * News Sources Routes
     */
    Route::prefix('sources')->name('sources.')->group(function () {
        Route::get('', [NewsSourceController::class, 'index'])->name('index');
    });

    /**
     * Categories Routes
     */
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('', [CategoryController::class, 'index'])->name('index');
    });

    /**
     * Authors Routes
     */
    Route::prefix('authors')->name('authors.')->group(function () {
        Route::get('', [AuthorController::class, 'index'])->name('index');
    });

    /**
     * Articles Routes
     */
    Route::prefix('articles')->name('articles.')->group(function () {
        Route::get('', [ArticleController::class, 'index'])->name('index');
    });

});
