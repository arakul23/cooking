<?php

namespace App\Http\Resources;

use App\Models\RecipeCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RecipeCategory */
class RecipeCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'recipes_count' => $this->whenHas('recipes_count'),
            'recipes' => RecipeResource::collection($this->whenLoaded('recipes'))
        ];
    }
}
