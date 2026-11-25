<?php

declare(strict_types=1);

namespace App\Service;

use App\Http\Requests\Recipe\ShowRequest;
use App\Models\Recipe;

class RecipeDetailService
{
    public function __construct(
        private readonly RatingService $ratingService,
        private readonly RecipeUniqueViewService $uniqueViewService,
    ) {}

    public function prepareForShow(ShowRequest $request, Recipe $recipe): Recipe
    {
        $validated = $request->validated();
        $locale = $validated['locale'] ?? app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        $recipe->rating_avg = $this->ratingService->refreshAverageRating($recipe);
        $recipe->increment('views');
        $this->uniqueViewService->record(
            $recipe,
            $request->user()?->getAuthIdentifier() === null
                ? null
                : (string) $request->user()->getAuthIdentifier(),
            $validated['visitor_id'] ?? null,
            $request->ip(),
            $request->userAgent(),
        );

        return $recipe->load([
            'categories',
            'products',
            'recipe_ratings',
            'translations' => function ($query) use ($locale, $fallbackLocale): void {
                $query
                    ->whereIn('locale', array_unique([$locale, $fallbackLocale]))
                    ->reorder()
                    ->orderByRaw('CASE WHEN locale = ? THEN 0 ELSE 1 END', [$locale]);
            },
        ])->loadCount('uniqueViews');
    }
}
