<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class RecipePdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_uses_the_requested_recipe_translation(): void
    {
        $recipe = Recipe::create([
            'portions' => 2,
            'calories' => 250,
        ]);
        $recipe->translations()->createMany([
            ['locale' => 'en', 'key' => 'title', 'value' => 'English title'],
            ['locale' => 'uk', 'key' => 'title', 'value' => 'Українська назва'],
            [
                'locale' => 'uk',
                'key' => 'content',
                'value' => '<p><strong>Крок 1</strong></p><script>alert(1)</script><p onclick="alert(1)">Крок 2</p>',
            ],
        ]);

        $pdf = Mockery::mock(DomPdf::class);
        $pdf->shouldReceive('setPaper')->once()->with('a4')->andReturnSelf();
        $pdf->shouldReceive('download')
            ->once()
            ->with(Mockery::on(fn (string $filename): bool => str_ends_with($filename, '.pdf')))
            ->andReturn(response('PDF content', 200, ['Content-Type' => 'application/pdf']));

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('recipes.pdf', Mockery::on(
                fn (array $data): bool => $data['title'] === 'Українська назва'
                    && $data['locale'] === 'uk'
                    && $data['content'] === '<p><strong>Крок 1</strong></p><p>Крок 2</p>'
                    && $data['labels']['ingredients'] === 'Інгредієнти'
            ))
            ->andReturn($pdf);

        $this->get("/api/recipes/{$recipe->id}/pdf?locale=uk")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertSeeText('PDF content');
    }

    public function test_export_rejects_unsupported_locales(): void
    {
        $recipe = Recipe::create([]);

        $this->getJson("/api/recipes/{$recipe->id}/pdf?locale=fr")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('locale');
    }

    public function test_export_returns_a_pdf_download(): void
    {
        $recipe = Recipe::create([]);
        $category = RecipeCategory::create(['name' => 'Dessert']);
        $product = Product::create(['name' => 'Flour']);
        $unitId = DB::table('units')->insertGetId([
            'code' => 'g',
            'name' => 'grams',
            'type' => 'weight',
            'to_base_factor' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $recipe->categories()->attach($category);
        $recipe->products()->attach($product, [
            'amount' => 150,
            'unit_id' => $unitId,
            'amount_base' => 150,
        ]);
        $recipe->translations()->createMany([
            ['locale' => 'uk', 'key' => 'title', 'value' => 'Рецепт'],
            ['locale' => 'uk', 'key' => 'content', 'value' => 'Кроки приготування'],
        ]);

        $response = $this->get("/api/recipes/{$recipe->id}/pdf?locale=uk");

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
