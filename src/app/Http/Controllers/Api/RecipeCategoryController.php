<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeCategoryResource;
use App\Models\RecipeCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecipeCategoryController extends Controller
{
    private const int MAX_RANDOM_CATEGORIES = 6;

    public function index(): AnonymousResourceCollection
    {
        $categories = RecipeCategory::with('recipes')->get()->map(function ($category) {
            $category['recipes_count'] = $category->recipes->count();
            return $category;
        });

        return RecipeCategoryResource::collection($categories);
    }

    public function show(RecipeCategory $category): RecipeCategoryResource
    {
        $category->load('recipes.translations');

        return RecipeCategoryResource::make($category);
    }

    public function getRandomCategories(): AnonymousResourceCollection
    {
        return RecipeCategoryResource::collection(RecipeCategory::has('recipes')->inRandomOrder()->limit(self::MAX_RANDOM_CATEGORIES)->get());
    }
}
