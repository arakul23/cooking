<?php

namespace App\Jobs;

use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Service\UserAgentGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class RecipeDataParserJob implements ShouldQueue
{
    use Queueable;

    private string $userAgent;

    /**
     * Create a new job instance.
     */
    public function __construct(readonly private string $url, readonly private string $categoryName)
    {
        $this->userAgent = UserAgentGenerator::getRandom();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $recipeProducts = [];
        $content = '';
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $this->url, [
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            ]
        ]);
        $crawler = new Crawler($response->getContent());
        $recipeHtml = $crawler->filter('table.recipe_new');
        $title = $recipeHtml->filter('h1.title')->text();
        $subInfoHtml = $recipeHtml->filter('div.sub_info');
        $subInfoHtml = str_replace("\xC2\xA0", ' ', $subInfoHtml->text());   // \u00A0
        $subInfoHtml = preg_replace('/\s+/u', ' ', trim($subInfoHtml));

        $productsHtml = $recipeHtml->filter('table.ingr_block .padding_l.padding_r');
        $contentHtml = $recipeHtml->filter('div.step_images_n div.step_n p');

        $hoursText = null;

        if (preg_match('/\b(\d+)\s*час(?:а|ов)?\b/ui', $subInfoHtml, $m)) {
            $hoursText = $m[1] . ' час' . ((int)$m[1] === 1 ? '' : ((int)$m[1] < 5 ? 'а' : 'ов'));
        }

        $minutes = $this->toMinutes($subInfoHtml);
        Log::channel('stderr')->info("$minutes");
        Log::channel('stderr')->info("$subInfoHtml");

        if (preg_match('/\b(\d+)\s*порц(?:ия|ии|ий)\b/ui', $subInfoHtml, $match)) {
            $portions = (int)$match[1];
        }

        foreach ($contentHtml as $contentNode) {
            $content .= trim($contentNode->nodeValue) . PHP_EOL;
        }

        foreach ($productsHtml as $productNode) {
            $explodedProductText = explode('-', $productNode->nodeValue);
            $recipeProducts[] = ['name' => trim($explodedProductText[0])];
        }

        $recipe = Recipe::create([
            'title' => $title,
            'logo' => 'https://placehold.co/1200x800?text=Temp+Image',
            'portions' => $portions ?? null,
            'description' => fake()->sentence(),
            'products' => $recipeProducts,
            'content' => $content,
            'total_time_minutes' => $minutes,
            'time_raw' => $hoursText
        ]);

        $category = RecipeCategory::firstOrCreate(
            ['name' => $this->categoryName]
        );

        $recipe->categories()->attach($category);
    }

    private function toMinutes(string $raw): ?int
    {
        // Нормализация пробелов (включая NBSP)
        $raw = str_replace("\xC2\xA0", ' ', mb_strtolower($raw));
        $raw = preg_replace('/\s+/u', ' ', trim($raw));

        if (preg_match('/\b(?:(\d+)\s*час(?:а|ов)?(?:\s+(\d+)\s*мин(?:ут[аы]?|\.?)?)?|(\d+)\s*мин(?:ут[аы]?|\.?)?)\b/u', $raw, $m)) {
            $hours = ($m[1] ?? '') !== '' ? (int)$m[1] : 0;
            $mins  = ($m[2] ?? '') !== '' ? (int)$m[2] : (int)($m[3] ?? 0);

            return $hours * 60 + $mins;
        }

        return null;
    }
}
