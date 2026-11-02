<?php

declare(strict_types=1);

namespace App\Service;

use App\Jobs\TranslateRecipeData;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class RecipeService
{
    public function createRecipe(array $data): Recipe
    {
        $locale = $data['locale'] ?? 'en';
        $categories = $data['categories'] ?? [];
        $translations = [
            'title' => $data['title'],
            'description' => $data['description'],
            'content' => $data['content'],
        ];

        unset(
            $data['categories'],
            $data['locale'],
            $data['title'],
            $data['description'],
            $data['content'],
        );

        return DB::transaction(function () use ($data, $categories, $translations, $locale): Recipe {
            $recipe = Recipe::create($data);
            $recipe->categories()->sync($categories);
            $recipe->translations()->createMany(
                collect($translations)
                    ->map(fn (string $value, string $key) => [
                        'locale' => $locale,
                        'key' => $key,
                        'value' => $value,
                    ])
                    ->all()
            );

            TranslateRecipeData::dispatch($recipe->id, $locale)->afterCommit();

            return $recipe;
        });
    }
}
