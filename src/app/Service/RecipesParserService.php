<?php

declare(strict_types=1);

namespace App\Service;

use App\Http\Interfaces\SiteParserInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecipesParserService implements SiteParserInterface
{
    private HttpClientInterface $httpClient;

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
    }

    public function parse()
    {
        $categories = $this->parseCategories();

        $this->parseRecipesList($categories);
    }

    public function parseCategories(): array
    {
        $categoryUrls = [];

        $response = $this->httpClient->request('GET', config('app.site_parse'));
        $htmlContent = $response->getContent();
        $crawler = new Crawler($htmlContent);
        $baseUrl = parse_url(config('app.site_parse'));

        $crawler
            ->filter('table.rcpf')
            ->first()
            ->filter('dt a.resList')
            ->each(function (Crawler $recipeLink) use (&$categoryUrls, $baseUrl) {
                $categoryUrls[] = $baseUrl['scheme'] . '://'. $baseUrl['host'] . $recipeLink->attr('href');
            });

        return $categoryUrls;
    }

    public function parseRecipesList(array $categoryUrls)
    {
        $baseUrl = parse_url(config('app.site_parse'));

        foreach ($categoryUrls as $url) {
            $response = $this->httpClient->request('GET', $url);
            $crawler = new Crawler($response->getContent());
            $recipeElements = $crawler
                ->filter('div.recipe_list_new.recipe_list_new2')
                ->first()
                ->filter('[itemprop="itemListElement"] a');

            foreach ($recipeElements as $recipeElement) {
                $element = new Crawler($recipeElement);
                $this->parseRecipeData($baseUrl['scheme'] . '://'. $baseUrl['host'] . $element->attr('href'));
            }
        }
    }

    public function parseRecipeData(string $url)
    {
        $response = $this->httpClient->request('GET', $url);
        $crawler = new Crawler($response->getContent());
        $recipeHtml = $crawler->filter('table.recipe_new');
        $title = $recipeHtml->filter('h1.title')->text();
        $subInfo = $recipeHtml->filter('div.sub_info');
        $subInfo = str_replace("\xC2\xA0", ' ', $subInfo->text());   // \u00A0
        $subInfo = preg_replace('/\s+/u', ' ', trim($subInfo));
        if (preg_match('/\b(\d+)\s*порц(?:ия|ии|ий)\b/ui', $subInfo, $match)) {
            $portions = (int)$match[1];
        }
        dd($portions);
    }
}
