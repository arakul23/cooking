<?php

use App\Http\Controllers\Api\RecipeCategoryController;
use App\Http\Controllers\Api\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Laravel API works',
    ]);
});

Route::prefix('/recipes')->controller(RecipeController::class)->group(function () {
    Route::get('/', 'getRecipes');
    Route::get('/popular', 'getPopularRecipes');
});

Route::get('/categories/random', [RecipeCategoryController::class, 'getRandomCategories']);
