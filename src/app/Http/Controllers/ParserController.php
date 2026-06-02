<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Service\RecipesParserService;
class ParserController extends Controller
{
    public function __construct(readonly private RecipesParserService $recipesParser)
    {}

    public function parse()
    {
        $this->recipesParser->parse();
    }
}
