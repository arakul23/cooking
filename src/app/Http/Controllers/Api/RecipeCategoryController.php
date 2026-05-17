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

    public function getRandomCategories(): AnonymousResourceCollection
    {
        return RecipeCategoryResource::collection(RecipeCategory::inRandomOrder()->limit(self::MAX_RANDOM_CATEGORIES)->get());
    }
}
