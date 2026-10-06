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
        $translationValue = fn (string $key) => $translations->firstWhere('key', $key)?->value;

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
            'created_at' => $this->created_at,
            'categories' => RecipeCategoryResource::collection($this->whenLoaded('categories')),
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'amount' => $product->pivot->amount,
                        'amount_unit' => $product->pivot->amount_unit,
                        'unit_id' => $product->pivot->unit_id,
                        'amount_base' => $product->pivot->amount_base,
                        'note' => $product->pivot->note,
                    ];
                });
            }),
        ];
    }
}
