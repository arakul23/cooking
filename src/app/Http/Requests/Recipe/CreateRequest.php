<?php

namespace App\Http\Requests\Recipe;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'description' => 'required|string:max:255',
            'content' => 'required|string:max:255',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png',
            'portions' => 'nullable|integer',
            'calories' => 'nullable|integer',
            'total_time_minutes' => 'nullable|integer',
            'time_raw' => 'nullable|string|max:20',
            'locale' => ['sometimes', 'string', Rule::in(array_keys(config('app.available_locales')))],
            'categories' => 'required|array|min:1',
            'ingredients' => ['required', 'array', 'min:1', 'max:30'],
            'ingredients.*' => ['required', 'array:product_id,amount,unit_id'],
            'ingredients.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')],
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
