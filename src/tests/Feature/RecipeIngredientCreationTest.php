<?php

namespace Tests\Feature;

use App\Jobs\TranslateRecipeData;
use App\Models\Product;
use App\Models\RecipeCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RecipeIngredientCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_creation_saves_product_amount_and_measurement_unit(): void
    {
        Queue::fake();
        $category = RecipeCategory::create(['name' => 'Dessert']);
        $product = Product::create(['name' => 'Flour']);
        $unitId = DB::table('units')->insertGetId([
            'code' => 'kg',
            'name' => 'kilogram',
            'type' => 'mass',
            'to_base_factor' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson('/api/recipes/create', [
                'title' => 'Flour cake',
                'description' => 'A simple cake made with flour.',
                'content' => 'Mix and bake.',
                'locale' => 'en',
                'categories' => [$category->id],
                'ingredients' => [
                    ['product_id' => $product->id, 'amount' => 0.25, 'unit_id' => $unitId],
                ],
            ])
            ->assertCreated();

        $this->assertDatabaseHas('recipe_product', [
            'product_id' => $product->id,
            'amount' => 0.25,
            'unit_id' => $unitId,
            'amount_base' => 250,
        ]);
        Queue::assertPushed(TranslateRecipeData::class);
    }

    public function test_recipe_creation_rejects_duplicate_ingredients(): void
    {
        $category = RecipeCategory::create(['name' => 'Dessert']);
        $product = Product::create(['name' => 'Flour']);
        $unitId = DB::table('units')->insertGetId([
            'code' => 'g',
            'name' => 'gram',
            'type' => 'mass',
            'to_base_factor' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson('/api/recipes/create', [
                'title' => 'Flour cake',
                'description' => 'A simple cake made with flour.',
                'content' => 'Mix and bake.',
                'categories' => [$category->id],
                'ingredients' => [
                    ['product_id' => $product->id, 'amount' => 100, 'unit_id' => $unitId],
                    ['product_id' => $product->id, 'amount' => 200, 'unit_id' => $unitId],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ingredients.0.product_id', 'ingredients.1.product_id']);
    }
}
