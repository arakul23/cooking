<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Recipe;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Gemini\Enums\ModelVariation;
use Gemini\GeminiHelper;
use Gemini\Laravel\Facades\Gemini;

class TranslateRecipeData implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $backoff = 20;

    /**
     * Create a new job instance.
     */
    public function __construct(readonly private int $recipeId)
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $recipe = Recipe::with('translations')->find($this->recipeId);

        $sourceData = json_encode([
            $recipe->translations->pluck('value', 'key')->toArray()
        ], JSON_UNESCAPED_UNICODE);


        $prompt = str_replace('source_data', $sourceData, file_get_contents(config_path('prompt.txt')));

        $result = Gemini::generativeModel(model: config('gemini.model'))->generateContent($prompt)->json(true);
    }
}
