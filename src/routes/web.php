<?php

use App\Http\Controllers\ParserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/parse', [ParserController::class, 'parse'])->name('parse_recipes');
