<?php

namespace App\Jobs;

use App\Service\UserAgentGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecipeListParserJob implements ShouldQueue
{
    use Queueable;

    private string $userAgent;

    /**
     * Create a new job instance.
     */
    public function __construct(readonly private string $url)
    {
        $this->userAgent = UserAgentGenerator::getRandom();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $baseUrl = parse_url(config('app.site_parse'));
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $this->url, [
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            ]
        ]);

        $crawler = new Crawler($response->getContent());
        $categoryName = $crawler->filter('td.center_block h1.center_orange')->text();
        $recipeElements = $crawler
            ->filter('div.recipe_list_new.recipe_list_new2')
            ->first()
            ->filter('[itemprop="itemListElement"] a');
        Log::channel('stderr')->info("=== Начинаю парсить категорию: {$categoryName} ===");
        foreach ($recipeElements as $recipeElement) {
            $element = new Crawler($recipeElement);
            RecipeDataParserJob::dispatch($baseUrl['scheme'] . '://' . $baseUrl['host'] . $element->attr('href'), $categoryName);
        }
    }
}
