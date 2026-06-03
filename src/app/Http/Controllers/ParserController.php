<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\RecipeParserJob;
use App\Service\RecipesParserService;
class ParserController extends Controller
{
    public function __construct(readonly private RecipesParserService $recipesParser)
    {}

    public function parse()
    {
        RecipeParserJob::dispatch();

        $this->recipesParser->parse();
    }
}
