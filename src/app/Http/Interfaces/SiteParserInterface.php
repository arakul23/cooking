<?php

declare(strict_types=1);

namespace App\Http\Interfaces;

interface SiteParserInterface
{
    public function parseCategories(): array;

    public function parseRecipesList(array $categoryUrls);

    public function parseRecipeData(string $url);
}
