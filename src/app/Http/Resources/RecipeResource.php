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
        ];
    }
}
