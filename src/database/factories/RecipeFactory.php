<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'logo' => 'https://placehold.co/1200x800?text=Temp+Image',
            'description' => fake()->paragraph(),
            'content' => implode("\n\n", fake()->paragraphs()),
            'portions' => fake()->numberBetween(1, 8),
            'calories' => fake()->numberBetween(150, 1000),
        ];
    }
}
