<?php

namespace App\Jobs;

use Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class ProductParserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const int CHUNK_SIZE = 500;

    public int $timeout = 3600;


    private const string FILE_PATH = '/media/arakul/Новый том/products.jsonl';

    public function handle(): void
    {
        $chunk = [];
        $stats = [
            'total' => 0,
            'imported' => 0,
            'skipped_no_name' => 0,
            'skipped_no_nutrients' => 0,
            'skipped_bad_json' => 0,
        ];

        foreach ($this->readLines(self::FILE_PATH) as $lineNumber => $line) {
            $stats['total']++;

            $product = json_decode($line, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($product)) {
                $stats['skipped_bad_json']++;
                Log::warning('Продукт: невалидный JSON', [
                    'line' => $lineNumber,
                    'json_error' => json_last_error_msg(),
                ]);
                continue;
            }

            $row = $this->mapProduct($product, $lineNumber, $stats);

            if ($row === null) {
                continue;
            }

            $chunk[] = $row;
            $stats['imported']++;

            if (count($chunk) >= self::CHUNK_SIZE) {
                $this->insertChunk($chunk);
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            $this->insertChunk($chunk);
        }

        Log::info('Импорт продуктов завершён', $stats);
    }

    /**
     * Превращает сырой массив продукта OFF в массив для вставки в БД.
     * Возвращает null, если продукт непригоден для импорта (лог пишется внутри).
     */
    private function mapProduct(array $product, int $lineNumber, array &$stats): ?array
    {
        $externalId = $product['_id'] ?? $product['code'] ?? null;

        $name = $this->extractName($product);

        if (empty($name)) {
            $stats['skipped_no_name']++;
            Log::warning('Продукт пропущен: нет названия', [
                'line' => $lineNumber,
                'external_id' => $externalId,
            ]);
            return null;
        }

        $nutrients = $this->extractNutrients($product);

        // Считаем продукт непригодным, если нет вообще ни одного значения
        if ($nutrients['kcal'] === null && $nutrients['proteins'] === null
            && $nutrients['fat'] === null && $nutrients['carbohydrates'] === null) {
            $stats['skipped_no_nutrients']++;
            Log::warning('Продукт пропущен: нет данных по нутриентам', [
                'line' => $lineNumber,
                'external_id' => $externalId,
                'name' => $name,
            ]);
            return null;
        }

        // Если есть только часть данных — не пропускаем, но фиксируем в лог для наблюдения
        if (in_array(null, $nutrients, true)) {
            Log::info('Продукт импортирован частично', [
                'line' => $lineNumber,
                'external_id' => $externalId,
                'name' => $name,
                'nutrients' => $nutrients,
            ]);
        }

        return [
            'name'          => $name,
            'brand'         => $product['brands'] ?? null,
            'kcal'          => $nutrients['kcal'],
            'protein'       => $nutrients['proteins'],
            'fat'           => $nutrients['fat'],
            'carbohydrates' => $nutrients['carbohydrates'],
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }

    /**
     * Достаёт название продукта с фолбэком по языкам.
     */
    private function extractName(array $product): ?string
    {
        $preferredLang = 'en';

        if (!empty($product["product_name_{$preferredLang}"])) {
            return $product["product_name_{$preferredLang}"];
        }

        $lc = $product['lc'] ?? null;
        if ($lc && !empty($product["product_name_{$lc}"])) {
            return $product["product_name_{$lc}"];
        }

        if (!empty($product['product_name'])) {
            return $product['product_name'];
        }

        if (!empty($product['generic_name'])) {
            return $product['generic_name'];
        }

        return null;
    }

    /**
     * Достаёт нутриенты, проверяя несколько возможных путей
     * (структура OFF менялась годами, ключи не унифицированы).
     */
    private function extractNutrients(array $product): array
    {
        return [
            'kcal' => $this->getNutrientValue($product, [
                'nutrition.aggregated_set.nutrients.energy-kcal.value',
                'nutriments.energy-kcal_100g',
                'nutriments.energy-kcal_value',
                'nutriments.energy-kcal',
            ]),
            'proteins' => $this->getNutrientValue($product, [
                'nutrition.aggregated_set.nutrients.proteins.value',
                'nutriments.proteins_100g',
                'nutriments.proteins_value',
                'nutriments.proteins',
            ]),
            'fat' => $this->getNutrientValue($product, [
                'nutrition.aggregated_set.nutrients.fat.value',
                'nutriments.fat_100g',
                'nutriments.fat_value',
                'nutriments.fat',
            ]),
            'carbohydrates' => $this->getNutrientValue($product, [
                'nutrition.aggregated_set.nutrients.carbohydrates.value',
                'nutriments.carbohydrates_100g',
                'nutriments.carbohydrates_value',
                'nutriments.carbohydrates',
            ]),
        ];
    }

    /**
     * Проверяет список путей по очереди, возвращает первое непустое значение.
     */
    private function getNutrientValue(array $product, array $possiblePaths): ?float
    {
        foreach ($possiblePaths as $path) {
            $value = data_get($product, $path);

            if ($value !== null && $value !== '') {
                return (float) $value;
            }
        }

        return null;
    }

    private function insertChunk(array $chunk): void
    {
        try {
            Product::insert($chunk);
        } catch (\Throwable $e) {
            Log::error('Ошибка при вставке чанка продуктов', [
                'error' => $e->getMessage(),
                'chunk_size' => count($chunk),
            ]);
        }
    }

    /**
     * Генератор построчного чтения файла. Отдаёт [номер_строки => строка].
     */
    private function readLines(string $filename): Generator
    {
        $file = fopen($filename, 'r');

        if ($file === false) {
            Log::error('Не удалось открыть файл', ['path' => $filename]);
            return;
        }

        $lineNumber = 0;

        while (($line = fgets($file)) !== false) {
            $lineNumber++;

            $line = trim($line);
            if ($line === '') {
                continue;
            }

            yield $lineNumber => $line;
        }

        fclose($file);
    }
}
