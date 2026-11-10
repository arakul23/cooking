<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductNameAndUnitLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_names_are_sanitized_when_saved_and_when_loaded(): void
    {
        $product = Product::create(['name' => '  Огірок! "свіжий" 🥒-зелений  ']);

        $this->assertSame('Огірок "свіжий" зелений', $product->name);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Огірок "свіжий" зелений',
        ]);

        $importedProductId = DB::table('products')->insertGetId([
            'name' => 'Mango! "Yellow"',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('Mango "Yellow"', Product::findOrFail($importedProductId)->name);
        $this->getJson('/api/recipes/fridge-search/products?search=Mang')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Mango "Yellow"');
    }

    public function test_unit_names_use_the_requested_locale_in_options_and_recipe_details(): void
    {
        $unitId = DB::table('units')->insertGetId([
            'code' => 'kg',
            'name' => 'kilogram',
            'type' => 'mass',
            'to_base_factor' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $recipe = Recipe::create([]);
        $product = Product::create(['name' => 'Flour']);
        $recipe->products()->attach($product, [
            'amount' => 2,
            'unit_id' => $unitId,
            'amount_base' => 2000,
        ]);

        $this->getJson('/api/recipes/fridge-search/options?locale=uk')
            ->assertOk()
            ->assertJsonPath('units.0.name', 'кг');

        $this->getJson("/api/recipes/{$recipe->id}?locale=uk")
            ->assertOk()
            ->assertJsonPath('data.products.0.amount_unit', '2 кг');
    }
}
