<?php

namespace App\Jobs;

use App\Service\UserAgentGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Tries(3)]
class RecipeCategoriesParserJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private HttpClientInterface $httpClient;

    private string $userAgent;
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->httpClient = HttpClient::create();
        $this->userAgent = UserAgentGenerator::getRandom();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', config('app.site_parse'), [
            'headers' => [
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            ]
        ]);

        $htmlContent = $response->getContent();
        $crawler = new Crawler($htmlContent);
        $baseUrl = parse_url(config('app.site_parse'));

        $crawler
            ->filter('table.rcpf')
            ->first()
            ->filter('dt a.resList')
            ->each(function (Crawler $recipeLink) use ($baseUrl) {
                RecipeListParserJob::dispatch($baseUrl['scheme'] . '://'. $baseUrl['host'] . $recipeLink->attr('href'), $recipeLink->filter('b'));
            });
    }
}
