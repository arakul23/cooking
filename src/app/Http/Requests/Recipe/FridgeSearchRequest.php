<?php

declare(strict_types=1);

namespace App\Http\Requests\Recipe;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FridgeSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('recipe_categories', 'id')],
            'locale' => ['sometimes', 'string', Rule::in(array_keys(config('app.available_locales')))],
            'ingredients' => ['required', 'array', 'min:1', 'max:30'],
            'ingredients.*' => ['required', 'array:product_id,amount,unit_id'],
            'ingredients.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id'),
            ],
            'ingredients.*.amount' => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:1000000'],
            'ingredients.*.unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')
                    ->where(fn (QueryBuilder $query) => $query->where('to_base_factor', '>', 0)),
            ],
        ];
    }
}
