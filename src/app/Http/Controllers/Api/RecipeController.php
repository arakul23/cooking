<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotification;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;

class RecipeController extends Controller
{
    public function getRecipes(): Collection
    {
        /*SendNotification::dispatch()->onQueue('testqueue');*/

        return Recipe::with('categories')->get();
    }

    public function getPopularRecipes(): Collection
    {
        return Recipe::with('categories')->limit(5)->get()->sortByDesc('views');
    }
}
