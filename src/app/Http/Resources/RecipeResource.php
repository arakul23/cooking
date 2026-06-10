<?php

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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'logo' => $this->logo,
            'description' => $this->description,
            'content' => $this->content,
            'portions' => $this->portions,
            'calories' => $this->calories,
            'views' => $this->views,
            'created_at' => $this->created_at,
            'categories' => RecipeCategoryResource::collection($this->whenLoaded('categories')),
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'pivot' => [
                            'amount' => $product->pivot->amount,
                            'unit_id' => $product->pivot->unit_id,
                            'amount_base' => $product->pivot->amount_base,
                            'note' => $product->pivot->note,
                        ],
                    ];
                });
            }),
        ];
    }
}
