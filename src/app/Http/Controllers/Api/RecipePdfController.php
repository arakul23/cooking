<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\ExportPdfRequest;
use App\Models\Recipe;
use Barryvdh\DomPDF\Facade\Pdf;
use DOMDocument;
use DOMNode;
use RuntimeException;

class RecipePdfController extends Controller
{
    private const array LABELS = [
        'en' => [
            'categories' => 'Categories',
            'portions' => 'Portions',
            'calories' => 'Calories',
            'cooking_time' => 'Cooking time',
            'minutes' => 'min',
            'rating' => 'Rating',
            'ingredients' => 'Ingredients',
            'instructions' => 'Instructions',
        ],
        'uk' => [
            'categories' => 'Категорії',
            'portions' => 'Порції',
            'calories' => 'Калорії',
            'cooking_time' => 'Час приготування',
            'minutes' => 'хв',
            'rating' => 'Оцінка',
            'ingredients' => 'Інгредієнти',
            'instructions' => 'Приготування',
        ],
    ];

    public function __invoke(ExportPdfRequest $request, Recipe $recipe)
    {
        $locale = $request->validated('locale')
            ?? (in_array(app()->getLocale(), ['en', 'uk'], true) ? app()->getLocale() : 'en');

        $recipe->load([
            'categories',
            'products',
            'translations',
            'recipe_ratings',
        ]);

        $translations = $recipe->translations->groupBy('locale');
        $selectedTranslations = $translations->get($locale, collect())->keyBy('key');
        $fallbackTranslations = $translations->get(config('app.fallback_locale'), collect())->keyBy('key');
        $translation = static fn (string $key): string => (string) (
            $selectedTranslations->get($key)?->value
            ?? $fallbackTranslations->get($key)?->value
            ?? ''
        );

        $content = $this->sanitizeContent($translation('content'));

        return Pdf::loadView('recipes.pdf', [
            'recipe' => $recipe,
            'title' => $translation('title'),
            'description' => $translation('description'),
            'content' => $content,
            'locale' => $locale,
            'categories' => $recipe->categories->pluck('name'),
            'products' => $recipe->products->map(fn ($product) => [
                'name' => $product->name,
                'amount' => $product->pivot->amount,
                'unit' => $product->pivot->unit?->localizedName($locale),
                'note' => $product->pivot->note,
            ]),
            'ratingCount' => $recipe->recipe_ratings->count(),
            'labels' => self::LABELS[$locale],
        ])
            ->setPaper('a4')
            ->download("{$translation('title')}.pdf");
    }

    private function sanitizeContent(string $content): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorSetting = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$content.'</body></html>',
                LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorSetting);
        }

        if (! $loaded) {
            throw new RuntimeException('Recipe instructions could not be parsed as HTML.');
        }

        $body = $document->getElementsByTagName('body')->item(0);

        if ($body === null) {
            throw new RuntimeException('Recipe instructions did not contain an HTML body.');
        }

        $html = '';

        foreach ($body->childNodes as $child) {
            $html .= $this->sanitizeNode($child);
        }

        return $html;
    }

    private function sanitizeNode(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return htmlspecialchars($node->nodeValue ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return '';
        }

        $tag = strtolower($node->nodeName);

        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'svg'], true)) {
            return '';
        }

        $html = '';

        foreach ($node->childNodes as $child) {
            $html .= $this->sanitizeNode($child);
        }

        $allowedTags = [
            'blockquote',
            'br',
            'code',
            'em',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'li',
            'ol',
            'p',
            'pre',
            's',
            'strong',
            'u',
            'ul',
        ];

        if (! in_array($tag, $allowedTags, true)) {
            return $html;
        }

        if ($tag === 'br') {
            return '<br>';
        }

        return "<{$tag}>{$html}</{$tag}>";
    }
}
