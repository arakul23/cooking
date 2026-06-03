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

    public function parseRecipesList(array $categoryUrls): array
    {
        $baseUrl = parse_url(config('app.site_parse'));
        $recipes = [];

        foreach ($categoryUrls as $url) {
            $response = $this->httpClient->request('GET', $url);
            $crawler = new Crawler($response->getContent());
            $recipeElements = $crawler
                ->filter('div.recipe_list_new.recipe_list_new2')
                ->first()
                ->filter('[itemprop="itemListElement"] a');

            foreach ($recipeElements as $recipeElement) {
                $element = new Crawler($recipeElement);
                $recipes[] = $this->parseRecipeData($baseUrl['scheme'] . '://'. $baseUrl['host'] . $element->attr('href'));
            }
        }

        return $recipes;
    }

    public function parseRecipeData(string $url): array
    {
        $recipeProducts = [];
        $content = '';
        $response = $this->httpClient->request('GET', $url);
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

        return [
            'title' => $title,
            'logo' => 'https://placehold.co/1200x800?text=Temp+Image',
            'portions' => $portions ?? null,
            'description' => fake()->sentence(),
            'products' => $recipeProducts,
            'content' => $content,
            'total_time_minues' => $minutes,
            'time_raw' => $hoursText
        ];
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
