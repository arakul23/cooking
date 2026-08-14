<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    private array $supportedLanguages = ['en', 'uk'];

    public function setLanguage(Request $request)
    {
        $language = $request->input('language');

        if (in_array($language, $this->supportedLanguages)) {
            session(['language' => $language]);
            app()->setLocale($language);
        }

        return response()->json(['message' => 'Language set successfully']);
    }
}
