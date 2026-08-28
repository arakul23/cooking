<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Recipe;

class RatingService
{
    public function calculateAverageRating(Recipe $recipe): float
    {
        return round(floatval($recipe->ratingByUsers()->avg('rating')), 2);
    }

    public function updateAverageRating(Recipe $recipe, float $rating): void
    {
        $recipe->ratingByUsers()->syncWithoutDetaching([
            auth()->id() ?? 1 => ['rating' => $rating]
        ]);
        $avgRating = $this->calculateAverageRating($recipe);
    }
}
