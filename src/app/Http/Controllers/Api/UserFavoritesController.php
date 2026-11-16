<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserFavoritesController extends Controller
{
    public function index(Request $request)
    {
        $recipes = $request->user()
            ->favouriteRecipes()
            ->with(['translations', 'categories'])
            ->get();

        return response()->json(
            RecipeResource::collection($recipes)->resolve($request)
        );
    }

    public function addFavouriteRecipeToUser(Request $request, Recipe $recipe): Response
    {
        $request->user()
            ->favouriteRecipes()
            ->syncWithoutDetaching([$recipe->id]);

        return response()->noContent();
    }

    public function removeFavouriteRecipeFromUser(Request $request, Recipe $recipe): Response
    {
        $request->user()
            ->favouriteRecipes()
            ->detach($recipe->id);

        return response()->noContent();
    }
}
