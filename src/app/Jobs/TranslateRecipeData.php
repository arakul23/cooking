<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Recipe;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

class TranslateRecipeData implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public int $timeout = 45;

    public function __construct(
        private readonly int $recipeId,
        private readonly ?string $sourceLocale = null,
    ) {}

    public function handle(): void
    {
        $recipe = Recipe::with('translations')->find($this->recipeId);

        if ($recipe === null) {
            Log::warning('Recipe translation skipped because the recipe no longer exists.', [
                'recipe_id' => $this->recipeId,
            ]);

            return;
        }

        $sourceLocale = $this->sourceLocale ?? $recipe->translations->first()?->locale;
        $sourceData = $recipe->translations
            ->where('locale', $sourceLocale)
            ->pluck('value', 'key')
            ->only(['title', 'description', 'content'])
            ->all();

        if ($sourceLocale === null || count($sourceData) !== 3) {
            Log::warning('Recipe translation skipped because the source translation is incomplete.', [
                'recipe_id' => $this->recipeId,
                'locale' => $sourceLocale,
            ]);

            return;
        }

        $promptTemplate = file_get_contents(config_path('prompts/translationPrompt.txt'));

        if ($promptTemplate === false) {
            throw new RuntimeException('The recipe translation prompt could not be read.');
        }

        $prompt = str_replace(
            'source_data',
            json_encode($sourceData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            $promptTemplate
        );
        $translations = Gemini::generativeModel(model: config('gemini.model'))
            ->generateContent($prompt)
            ->json(true);

        if (! is_array($translations)) {
            throw new UnexpectedValueException('Gemini returned an invalid recipe translation response.');
        }

        foreach (['en', 'uk'] as $locale) {
            $translation = $translations[$locale] ?? null;

            if (! is_array($translation)) {
                throw new UnexpectedValueException("Gemini did not return a {$locale} recipe translation.");
            }

            foreach (['title', 'description', 'content'] as $key) {
                if (! is_string($translation[$key] ?? null)) {
                    throw new UnexpectedValueException("Gemini returned an invalid {$locale} recipe {$key}.");
                }
            }

            DB::transaction(function () use ($recipe, $locale, $translation): void {
                foreach (['title', 'description', 'content'] as $key) {
                    $recipe->translations()->updateOrCreate(
                        ['locale' => $locale, 'key' => $key],
                        ['value' => $translation[$key]]
                    );
                }
            });
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Recipe translation failed after the maximum number of attempts.', [
            'recipe_id' => $this->recipeId,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }
}
