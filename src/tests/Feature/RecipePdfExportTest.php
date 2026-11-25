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
                'value' => '<p><strong>Крок 1</strong></p><script>alert(1)</script><p onclick="alert(1)">Крок 2 &amp; &lt; &gt; "тест" 😀</p><img src="http://127.0.0.1:9/track">',
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
                    && $data['content'] === '<p><strong>Крок 1</strong></p><p>Крок 2 &amp; &lt; &gt; &quot;тест&quot; 😀</p>'
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

    public function test_export_falls_back_to_an_available_translation_when_requested_and_default_locales_are_missing(): void
    {
        $recipe = Recipe::create([]);
        $recipe->translations()->createMany([
            ['locale' => 'uk', 'key' => 'title', 'value' => 'Єдина назва'],
            ['locale' => 'uk', 'key' => 'content', 'value' => '<p>Єдиний текст</p>'],
        ]);

        $pdf = Mockery::mock(DomPdf::class);
        $pdf->shouldReceive('setPaper')->once()->with('a4')->andReturnSelf();
        $pdf->shouldReceive('download')->once()->andReturn(
            response('PDF content', 200, ['Content-Type' => 'application/pdf'])
        );

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('recipes.pdf', Mockery::on(
                fn (array $data): bool => $data['title'] === 'Єдина назва'
                    && $data['content'] === '<p>Єдиний текст</p>'
                    && $data['labels']['ingredients'] === 'Ingredients'
            ))
            ->andReturn($pdf);

        $this->get('/api/recipes/'.$recipe->id.'/pdf?locale=en')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
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
            ['locale' => 'uk', 'key' => 'title', 'value' => 'Рецепт / "café"'],
            ['locale' => 'uk', 'key' => 'content', 'value' => 'Кроки приготування'],
        ]);

        $response = $this->get("/api/recipes/{$recipe->id}/pdf?locale=uk");

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment;', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('filename*=utf-8\'\'', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('%22caf%C3%A9%22.pdf', $response->headers->get('content-disposition'));
        $this->assertStringNotContainsString('%2F', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_export_handles_empty_and_heavy_multilingual_recipes_without_n_plus_one_unit_queries(): void
    {
        $emptyRecipe = Recipe::create([]);
        $this->get("/api/recipes/{$emptyRecipe->id}/pdf")
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $recipe = Recipe::create(['logo' => 'https://invalid.example/very-large.webp']);
        $title = str_repeat('éüłКириллица', 17);
        $content = '<p>Зображення: <img src="https://invalid.example/missing.png"></p><ol>'
            .implode('', array_map(
                fn (int $step): string => '<li>Крок '.$step.': '.str_repeat('опис і дія ', 16).'</li>',
                range(1, 35)
            ))
            .'</ol><script>alert(1)</script>';
        $recipe->translations()->createMany([
            ['locale' => 'uk', 'key' => 'title', 'value' => $title],
            ['locale' => 'uk', 'key' => 'description', 'value' => 'Опис & < > "текст" ½ ¾ 😀'],
            ['locale' => 'uk', 'key' => 'content', 'value' => $content],
        ]);

        $unitId = DB::table('units')->insertGetId([
            'code' => 'g',
            'name' => 'grams',
            'type' => 'weight',
            'to_base_factor' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $ingredients = [];

        for ($index = 1; $index <= 35; $index++) {
            $product = Product::create(['name' => "Product éüł ½ ¾ {$index} & < >"]);
            $ingredients[$product->id] = [
                'amount' => 150,
                'unit_id' => $unitId,
                'amount_base' => 150,
            ];
        }

        $recipe->products()->sync($ingredients);

        $connection = DB::connection();
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        try {
            $response = $this->get("/api/recipes/{$recipe->id}/pdf?locale=en");
            $queries = $connection->getQueryLog();
        } finally {
            $connection->disableQueryLog();
        }

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $pdf = $response->getContent();

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('/FontFile2', $pdf);
        preg_match_all('/\/Type\s*\/Page\b/', $pdf, $pages);
        $this->assertGreaterThan(1, count($pages[0]));
        $unitQueries = array_filter(
            $queries,
            fn (array $query): bool => str_contains(strtolower($query['query']), 'units')
        );
        $this->assertCount(1, $unitQueries);
    }

    public function test_export_returns_not_found_for_missing_or_deleted_recipes(): void
    {
        $recipe = Recipe::create([]);
        $recipeId = $recipe->id;
        $recipe->delete();

        $this->get("/api/recipes/{$recipeId}/pdf")->assertNotFound();
        $this->get('/api/recipes/999999/pdf')->assertNotFound();
    }

    public function test_export_is_rate_limited(): void
    {
        $recipe = Recipe::create([]);
        $pdf = Mockery::mock(DomPdf::class);
        $pdf->shouldReceive('setPaper')->times(10)->with('a4')->andReturnSelf();
        $pdf->shouldReceive('download')->times(10)->andReturn(
            response('PDF content', 200, ['Content-Type' => 'application/pdf'])
        );
        Pdf::shouldReceive('loadView')->times(10)->andReturn($pdf);

        for ($request = 0; $request < 10; $request++) {
            $this->get("/api/recipes/{$recipe->id}/pdf")->assertOk();
        }

        $this->get("/api/recipes/{$recipe->id}/pdf")->assertTooManyRequests();
    }
}
