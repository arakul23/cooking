<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\CreateRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use App\Service\RatingService;
use App\Service\RecipeService;
use http\Client\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecipeController extends Controller
{
    public function __construct(readonly private RatingService $ratingService)
    {
    }

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

    public function create(CreateRequest $request, RecipeService $recipeService): \Illuminate\Http\JsonResponse
    {
        $dataArray = $request->validated();

        if ($request->hasFile('logo')) {
            $dataArray['logo'] = $request->file('logo')->store('recipes/logos', 'public'); // сохранится что-то вроде "recipes/logos/abc123.jpg"
        }

        $recipe = $recipeService->createRecipe($dataArray);

        return response()->json($recipe, 201);
    }

    public function getRandomRecipe(): RecipeResource
    {
        return RecipeResource::make(Recipe::inRandomOrder()->with('translations')->limit(1)->first());
    }

    public function setRating(Request $request, Recipe $recipe)
    {
        $this->ratingService->updateAverageRating($recipe, $request->rating);
    }
}
