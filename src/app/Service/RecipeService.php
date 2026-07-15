<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\Recipe;

class RecipeService
{
    public function createRecipe(array $data): Recipe
    {
        $recipe = Recipe::create($data);

        $recipe->categories()->sync($data['categories']);
        $recipe->translations()->createMany([
            ['locale' => 'en', 'key' => 'title', 'value' => $data['title']],
            ['locale' => 'en', 'key' => 'description', 'value' => 'Классический борщ'],
            ['locale' => 'en', 'key' => 'content', 'value' => $data['description']],
        ]);

        return $recipe;
    }
}
