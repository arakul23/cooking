<?php

declare(strict_types=1);

namespace App\Service;

use App\Jobs\TranslateRecipeData;
use App\Models\Recipe;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class RecipeService
{
    public function createRecipe(array $data): Recipe
    {
        $locale = $data['locale'] ?? 'en';
        $categories = $data['categories'] ?? [];
        $ingredients = $data['ingredients'] ?? [];
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
            $data['ingredients'],
        );

        return DB::transaction(function () use ($data, $categories, $ingredients, $translations, $locale): Recipe {
            $recipe = Recipe::create($data);
            $recipe->categories()->sync($categories);
            if ($ingredients !== []) {
                $units = Unit::query()
                    ->whereIn('id', collect($ingredients)->pluck('unit_id')->unique())
                    ->get(['id', 'to_base_factor'])
                    ->keyBy('id');

                $recipe->products()->sync(collect($ingredients)->mapWithKeys(
                    fn (array $ingredient): array => [
                        $ingredient['product_id'] => [
                            'amount' => $ingredient['amount'],
                            'unit_id' => $ingredient['unit_id'],
                            'amount_base' => round(
                                (float) $ingredient['amount'] * (float) $units->get($ingredient['unit_id'])->to_base_factor,
                                3
                            ),
                        ],
                    ]
                )->all());
            }
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
