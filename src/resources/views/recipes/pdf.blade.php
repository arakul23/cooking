<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            color: #232d37;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        h1, h2 {
            color: #fc6c3f;
        }

        h1 {
            margin-bottom: 8px;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        h1, .content {
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        h2 {
            margin: 22px 0 8px;
            font-size: 16px;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        p, li {
            page-break-inside: avoid;
        }

        ul {
            padding-left: 20px;
        }

        ol {
            padding-left: 24px;
        }

        blockquote {
            margin-left: 0;
            padding-left: 12px;
            border-left: 3px solid #fc6c3f;
            color: #727272;
        }

        pre, code {
            font-family: "DejaVu Sans Mono", monospace;
        }

        .metadata {
            color: #727272;
        }

    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    @if (trim($description) !== '')
        <p>{{ $description }}</p>
    @endif

    @if ($categories->isNotEmpty())
        <p><strong>{{ $labels['categories'] }}:</strong> {{ $categories->implode(', ') }}</p>
    @endif

    @if ($recipe->portions !== null || $recipe->calories !== null || $recipe->total_time_minutes !== null || $ratingCount > 0)
        <p class="metadata">
            @if ($recipe->portions !== null)
                {{ $labels['portions'] }}: {{ $recipe->portions }}
            @endif
            @if ($recipe->calories !== null)
                &nbsp; {{ $labels['calories'] }}: {{ $recipe->calories }}
            @endif
            @if ($recipe->total_time_minutes !== null)
                &nbsp; {{ $labels['cooking_time'] }}: {{ $recipe->total_time_minutes }} {{ $labels['minutes'] }}
            @endif
            @if ($ratingCount > 0)
                &nbsp; {{ $labels['rating'] }}: {{ number_format((float) $recipe->rating_avg, 1) }} ({{ $ratingCount }})
            @endif
        </p>
    @endif

    @if ($products->isNotEmpty())
        <h2>{{ $labels['ingredients'] }}</h2>
        <ul>
            @foreach ($products as $product)
                <li>
                    {{ $product['name'] }}
                    @if ($product['amount'] !== null)
                        — {{ $product['amount'] }}@if ($product['unit'] !== '') {{ ' '.$product['unit'] }}@endif
                    @endif
                    @if ($product['note'])
                        ({{ $product['note'] }})
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @if (trim($content) !== '')
        <h2>{{ $labels['instructions'] }}</h2>
        <div class="content">{!! $content !!}</div>
    @endif
</body>
</html>
