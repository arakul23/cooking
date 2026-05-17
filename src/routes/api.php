<?php

use App\Http\Controllers\Api\RecipeCategoryController;
use App\Http\Controllers\Api\RecipeController;
use Illuminate\Support\Facades\Route;

Route::prefix('/recipes')->controller(RecipeController::class)->group(function () {
    Route::get('/', 'getRecipes');
    Route::get('/popular', 'getPopularRecipes');
    Route::get('/{recipe}', 'show');
});

Route::prefix('/categories')->controller(RecipeCategoryController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/random', 'getRandomCategories');
});
