<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class RatingService
{
    public function calculateAverageRating(Recipe $recipe): float
    {
        return round(floatval($recipe->ratingByUsers()->avg('rating')), 2);
    }

    public function refreshAverageRating(Recipe $recipe): float
    {
        return DB::transaction(function () use ($recipe): float {
            $recipe = Recipe::query()
                ->lockForUpdate()
                ->findOrFail($recipe->getKey());
            $averageRating = $this->calculateAverageRating($recipe);

            if ((float) $recipe->rating_avg !== $averageRating) {
                $recipe->rating_avg = $averageRating;
                $recipe->save();
            }

            return $averageRating;
        });
    }

    public function updateAverageRating(Recipe $recipe, int $userId, float $rating): float
    {
        return DB::transaction(function () use ($recipe, $userId, $rating): float {
            $recipe = Recipe::query()
                ->lockForUpdate()
                ->findOrFail($recipe->getKey());

            $recipe->ratingByUsers()->syncWithoutDetaching([
                $userId => ['rating' => $rating],
            ]);

            $averageRating = $this->calculateAverageRating($recipe);
            $recipe->rating_avg = $averageRating;
            $recipe->save();

            return $averageRating;
        });
    }
}
