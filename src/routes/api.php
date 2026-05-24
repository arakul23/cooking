<?php

use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\RecipeCategoryController;
use App\Http\Controllers\Api\RecipeController;
use Illuminate\Support\Facades\Route;

Route::prefix('/recipes')->controller(RecipeController::class)->group(function () {
    Route::get('/', 'getRecipes');
    Route::get('/popular', 'getPopularRecipes');
    Route::get('/random', 'getRandomRecipe');
    Route::get('/{recipe}', 'show')->whereNumber('recipe');
});

Route::prefix('/categories')->controller(RecipeCategoryController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/random', 'getRandomCategories');
    Route::get('/{category}', 'show');
});

Route::post('/contact/send', [ContactUsController::class, 'handleContactUsRequest']);
