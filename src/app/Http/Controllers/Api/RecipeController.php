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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecipeController extends Controller
{
    public function __construct(readonly private RatingService $ratingService)
    {
    }

    const int MAX_RANDOM_RECIPES = 5;

    public function getRecipes(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->query('search', ''));
        $recipes = Recipe::with(['categories', 'translations']);

        if ($search !== '') {
            $recipes->whereHas('translations', function ($query) use ($search) {
                $query->whereIn('key', ['title', 'description'])
                    ->where('value', 'like', "%{$search}%");
            });
        }

        return RecipeResource::collection($recipes->get());
    }

    public function getPopularRecipes(): AnonymousResourceCollection
    {
        return RecipeResource::collection(
            Recipe::with(['categories', 'translations'])
                ->limit(self::MAX_RANDOM_RECIPES)
                ->get()
                ->sortByDesc('views')
        );
    }

    public function show(Recipe $recipe): RecipeResource
    {
        $recipe->increment('views');
        $recipe->loadMissing(['categories', 'products', 'translations', 'recipe_ratings']);

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
