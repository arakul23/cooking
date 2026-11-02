<?php

namespace Tests\Feature;

use App\Jobs\TranslateRecipeData;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Service\RecipeService;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Resources\GenerativeModel;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RecipeTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_creation_saves_source_locale_and_dispatches_translation_job(): void
    {
        Queue::fake();
        $category = RecipeCategory::create(['name' => 'Dessert']);

        $recipe = app(RecipeService::class)->createRecipe([
            'title' => 'Торт',
            'description' => 'Шоколадний торт',
            'content' => 'Змішайте інгредієнти.',
            'locale' => 'uk',
            'categories' => [$category->id],
            'portions' => 6,
        ]);

        $this->assertDatabaseHas('translations', [
            'translatable_id' => $recipe->id,
            'locale' => 'uk',
            'key' => 'title',
            'value' => 'Торт',
        ]);
        $this->assertDatabaseHas('translations', [
            'translatable_id' => $recipe->id,
            'locale' => 'uk',
            'key' => 'content',
            'value' => 'Змішайте інгредієнти.',
        ]);
        Queue::assertPushed(TranslateRecipeData::class);
    }

    public function test_translation_job_saves_both_locales_and_updates_existing_source_translation(): void
    {
        $recipe = Recipe::create([]);
        $recipe->translations()->createMany([
            ['locale' => 'en', 'key' => 'title', 'value' => 'Original title'],
            ['locale' => 'en', 'key' => 'description', 'value' => 'Original description'],
            ['locale' => 'en', 'key' => 'content', 'value' => 'Original instructions'],
        ]);
        $translated = [
            'en' => [
                'title' => 'English title',
                'description' => 'English description',
                'content' => 'English instructions',
            ],
            'uk' => [
                'title' => 'Українська назва',
                'description' => 'Український опис',
                'content' => 'Українські інструкції',
            ],
        ];

        Gemini::fake([
            GenerateContentResponse::fake([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => json_encode($translated, JSON_UNESCAPED_UNICODE)]],
                        'role' => 'model',
                    ],
                ]],
                'usageMetadata' => ['promptTokenCount' => 1, 'totalTokenCount' => 1],
            ]),
        ]);

        (new TranslateRecipeData($recipe->id, 'en'))->handle();

        $this->assertSame(
            6,
            $recipe->translations()->count(),
            'Translations should update the source locale and add three Ukrainian translations.'
        );
        $this->assertDatabaseHas('translations', [
            'translatable_id' => $recipe->id,
            'locale' => 'en',
            'key' => 'title',
            'value' => 'English title',
        ]);
        $this->assertDatabaseHas('translations', [
            'translatable_id' => $recipe->id,
            'locale' => 'uk',
            'key' => 'content',
            'value' => 'Українські інструкції',
        ]);
        Gemini::assertSent(GenerativeModel::class);
    }

    public function test_translation_job_has_a_finite_retry_limit(): void
    {
        $job = new TranslateRecipeData(1);

        $this->assertSame(3, $job->tries);
        $this->assertSame([60, 300], $job->backoff);
    }

    public function test_recipe_endpoint_returns_translations_for_the_requested_locale(): void
    {
        $recipe = Recipe::create([]);
        $recipe->translations()->createMany([
            ['locale' => 'en', 'key' => 'title', 'value' => 'English title'],
            ['locale' => 'en', 'key' => 'description', 'value' => 'English description'],
            ['locale' => 'en', 'key' => 'content', 'value' => 'English instructions'],
            ['locale' => 'uk', 'key' => 'title', 'value' => 'Українська назва'],
            ['locale' => 'uk', 'key' => 'description', 'value' => 'Український опис'],
            ['locale' => 'uk', 'key' => 'content', 'value' => 'Українські інструкції'],
        ]);

        $this->getJson("/api/recipes/{$recipe->id}?locale=uk")
            ->assertOk()
            ->assertJsonPath('data.title', 'Українська назва')
            ->assertJsonPath('data.description', 'Український опис')
            ->assertJsonPath('data.content', 'Українські інструкції');
    }

    public function test_recipe_endpoint_uses_fallback_when_requested_translation_is_missing(): void
    {
        config(['app.fallback_locale' => 'en']);
        $recipe = Recipe::create([]);
        $recipe->translations()->createMany([
            ['locale' => 'en', 'key' => 'title', 'value' => 'English fallback'],
            ['locale' => 'en', 'key' => 'description', 'value' => 'English description fallback'],
            ['locale' => 'uk', 'key' => 'title', 'value' => 'Українська назва'],
        ]);

        $this->getJson("/api/recipes/{$recipe->id}?locale=uk")
            ->assertOk()
            ->assertJsonPath('data.title', 'Українська назва')
            ->assertJsonPath('data.description', 'English description fallback');
    }
}
