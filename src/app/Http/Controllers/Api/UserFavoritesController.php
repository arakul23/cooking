<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserFavoritesController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $user->load('favouriteRecipes.translations');

        return response()->json($user->favouriteRecipes);
    }

    public function addFavouriteRecipeToUser(Request $request, Recipe $recipe): Response
    {
        $request->user()
            ->favouriteRecipes()
            ->syncWithoutDetaching([$recipe->id]);

        return response()->noContent();
    }
}
