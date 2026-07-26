<?php

namespace App\Console\Commands;

use App\Jobs\ProductParserJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:parse-products')]
#[Description('Parse products from a json file')]
class ParseProducts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        ProductParserJob::dispatch();
    }
}
