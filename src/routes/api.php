<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\RecipeCategoryController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\UserFavoritesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum,api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);

Route::prefix('/recipes')->controller(RecipeController::class)->group(function () {
    Route::get('/', 'getRecipes');
    Route::get('/popular', 'getPopularRecipes');
    Route::get('/random', 'getRandomRecipe');
    Route::get('/{recipe}', 'show')->whereNumber('recipe');
    Route::post('/{recipe}/rating', 'setRating')->whereNumber('recipe');
    Route::post('/create', 'create');
});

Route::middleware('auth:api,sanctum')->prefix('/recipes/favourite')->controller(UserFavoritesController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/{recipe}', 'addFavouriteRecipeToUser')->whereNumber('recipe');
});

Route::prefix('/categories')->controller(RecipeCategoryController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/random', 'getRandomCategories');
    Route::get('/{category}', 'show');
});

Route::post('/contact/send', [ContactUsController::class, 'handleContactUsRequest']);
