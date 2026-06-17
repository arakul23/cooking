<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecipeController extends Controller
{
    const int MAX_RANDOM_RECIPES = 5;

    public function getRecipes(): Collection
    {
        return Recipe::with('categories')->get();
    }

    public function getPopularRecipes(): AnonymousResourceCollection
    {
        return RecipeResource::collection(
            Recipe::with('categories')
                ->limit(self::MAX_RANDOM_RECIPES)
                ->get()
                ->sortByDesc('views')
        );
    }

    public function show(Recipe $recipe): RecipeResource
    {
        $recipe->increment('views');
        $recipe->loadMissing(['categories', 'products', 'translations']);

        return RecipeResource::make($recipe);
    }

    public function getRandomRecipe(): RecipeResource
    {
        return RecipeResource::make(Recipe::inRandomOrder()->limit(1)->first());
    }
}
