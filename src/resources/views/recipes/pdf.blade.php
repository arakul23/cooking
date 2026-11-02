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
        }

        h2 {
            margin: 22px 0 8px;
            font-size: 16px;
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

        .content {
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    @if ($description !== '')
        <p>{{ $description }}</p>
    @endif

    @if ($categories->isNotEmpty())
        <p><strong>{{ $labels['categories'] }}:</strong> {{ $categories->implode(', ') }}</p>
    @endif

    <p class="metadata">
        @if ($recipe->portions)
            {{ $labels['portions'] }}: {{ $recipe->portions }}
        @endif
        @if ($recipe->calories)
            &nbsp; {{ $labels['calories'] }}: {{ $recipe->calories }}
        @endif
        @if ($recipe->total_time_minutes)
            &nbsp; {{ $labels['cooking_time'] }}: {{ $recipe->total_time_minutes }} {{ $labels['minutes'] }}
        @endif
        @if ($recipe->rating_avg)
            &nbsp; {{ $labels['rating'] }}: {{ number_format((float) $recipe->rating_avg, 1) }} ({{ $ratingCount }})
        @endif
    </p>

    @if ($products->isNotEmpty())
        <h2>{{ $labels['ingredients'] }}</h2>
        <ul>
            @foreach ($products as $product)
                <li>
                    {{ $product['name'] }}
                    @if ($product['amount'] !== null)
                        — {{ rtrim(rtrim((string) $product['amount'], '0'), '.') }} {{ $product['unit'] }}
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
