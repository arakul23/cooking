<?php

use App\Http\Controllers\Api\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Laravel API works',
    ]);
});

Route::get('/recipes', [RecipeController::class, 'getRecipes']);
Route::get('/recipes/popular', [RecipeController::class, 'getPopularRecipes']);
