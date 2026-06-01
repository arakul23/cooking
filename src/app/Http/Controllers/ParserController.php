<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class ParserController extends Controller
{
    public function parse()
    {
        $client = HttpClient::create();

        $response = $client->request('GET', config('app.site_parse'));
        $htmlContent = $response->getContent();
        $crawler = new Crawler($htmlContent);

        $crawler
            ->filter('table.rcpf')
            ->first()
            ->filter('dt a.resList')
            ->each(function (Crawler $recipeLink) {
                dump($recipeLink->attr('href'));
            });

    }
}
