<?php

namespace App\Console\Commands;

use App\Jobs\RecipeCategoriesParserJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:parse-recipes-command')]
#[Description('Command description')]
class ParseRecipes extends Command
{
    public function handle(): void
    {
        RecipeCategoriesParserJob::dispatch();
    }
}
