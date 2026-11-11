<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Recipe */
class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $translations = $this->translations;
        $locale = $request->input('locale', app()->getLocale());
        $translationValue = fn (string $key) => $translations->firstWhere('key', $key)?->value;
        $userRating = null;
        $ratingAvailableAt = null;

        if ($request->user() && $this->resource->relationLoaded('recipe_ratings')) {
            $userVote = $this->recipe_ratings->firstWhere('id', $request->user()->getAuthIdentifier());
            $userRating = $userVote?->pivot->rating;
            $ratingAvailableAt = $userVote?->pivot?->updated_at?->copy()->addMinute()->toIso8601String();
        }

        return [
            'id' => $this->id,
            'title' => $translationValue('title') ?? '',
            'logo' => $this->logo,
            'description' => $translationValue('description') ?? '',
            'content' => $translationValue('content') ?? '',
            'portions' => $this->portions,
            'calories' => $this->calories,
            'views' => $this->views,
            'rating_avg' => $this->rating_avg,
            'user_rating' => $userRating,
            'rating_available_at' => $ratingAvailableAt,
            'created_at' => $this->created_at,
            'categories' => RecipeCategoryResource::collection($this->whenLoaded('categories')),
            'missing_products' => $this->when(
                $this->resource->getAttribute('missing_products') !== null,
                $this->resource->getAttribute('missing_products')
            ),
            'products' => $this->whenLoaded('products', function () use ($locale) {
                return $this->products->map(function ($product) use ($locale) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'amount' => $product->pivot->amount,
                        'amount_unit' => $product->pivot->amountUnitForLocale($locale),
                        'unit_id' => $product->pivot->unit_id,
                        'amount_base' => $product->pivot->amount_base,
                        'note' => $product->pivot->note,
                    ];
                });
            }),
        ];
    }
}
