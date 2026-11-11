<?php

namespace App\Providers;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('create-recipe', function (User $user) {
            return true;
        });

        RateLimiter::for('recipe-rating', function (Request $request) {
            $recipe = $request->route('recipe');
            $recipeId = $recipe instanceof Recipe ? $recipe->getKey() : $recipe;
            $userId = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(1)->by($userId.':'.$recipeId);
        });
    }
}
