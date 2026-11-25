<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\CreateRequest;
use App\Http\Requests\Recipe\FridgeSearchOptionsRequest;
use App\Http\Requests\Recipe\FridgeSearchProductsRequest;
use App\Http\Requests\Recipe\FridgeSearchRequest;
use App\Http\Requests\Recipe\RatingRequest;
use App\Http\Requests\Recipe\ShowRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Models\Unit;
use App\Service\RatingService;
use App\Service\RecipeDetailService;
use App\Service\RecipeFridgeSearchService;
use App\Service\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecipeController extends Controller
{
    public function __construct(private readonly RatingService $ratingService) {}

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

    public function show(
        ShowRequest $request,
        Recipe $recipe,
        RecipeDetailService $recipeDetailService,
    ): RecipeResource {
        return RecipeResource::make($recipeDetailService->prepareForShow($request, $recipe));
    }

    public function create(CreateRequest $request, RecipeService $recipeService): JsonResponse
    {
        $dataArray = $request->validated();

        if ($request->hasFile('logo')) {
            $dataArray['logo'] = $request->file('logo')->store('recipes/logos', 'public');
        }

        $recipe = $recipeService->createRecipe($dataArray);

        return response()->json($recipe, 201);
    }

    public function getRandomRecipe(): RecipeResource
    {
        return RecipeResource::make(Recipe::inRandomOrder()->with('translations')->limit(1)->first());
    }

    public function fridgeSearchOptions(FridgeSearchOptionsRequest $request): JsonResponse
    {
        $locale = $request->validated('locale') ?? app()->getLocale();

        return response()->json([
            'units' => Unit::query()
                ->where('to_base_factor', '>', 0)
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'type'])
                ->map(fn (Unit $unit): array => [
                    'id' => $unit->id,
                    'name' => $unit->localizedName($locale),
                    'code' => $unit->code,
                    'type' => $unit->type,
                ]),
            'categories' => RecipeCategory::query()
                ->whereHas('recipes')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function fridgeSearchProducts(FridgeSearchProductsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (! isset($validated['search'])) {
            // Provide a bounded initial list; the full product catalog is loaded only as the user searches.
            return response()->json([
                'data' => Product::query()
                    ->orderBy('name')
                    ->limit(100)
                    ->get(['id', 'name'])
                    ->unique('name')
                    ->take(50)
                    ->values(),
            ]);
        }

        $search = Product::sanitizeName($validated['search']);

        if (mb_strlen($search) < 2) {
            return response()->json(['data' => []]);
        }

        return response()->json([
            'data' => $this->getFridgeSearchProductOptions($search),
        ]);
    }

    private function getFridgeSearchProductOptions(string $search)
    {
        return Product::query()
            ->where('name', 'like', $search.'%')
            ->groupBy('name')
            ->selectRaw('MIN(id) as id, name')
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name']);
    }

    public function fridgeSearch(FridgeSearchRequest $request, RecipeFridgeSearchService $searchService): AnonymousResourceCollection
    {
        return RecipeResource::collection($searchService->search($request->validated()));
    }

    public function setRating(RatingRequest $request, Recipe $recipe): JsonResponse
    {
        $rating = (float) $request->validated('rating');
        $ratingAverage = $this->ratingService->updateAverageRating(
            $recipe,
            (int) $request->user()->getAuthIdentifier(),
            $rating
        );

        return response()->json([
            'rating_avg' => $ratingAverage,
            'rating_count' => $recipe->ratingByUsers()->count(),
            'user_rating' => $rating,
            'rating_available_at' => now()->addMinute()->toIso8601String(),
        ]);
    }
}
