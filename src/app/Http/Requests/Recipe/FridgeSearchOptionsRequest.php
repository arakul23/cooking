<?php

declare(strict_types=1);

namespace App\Http\Requests\Recipe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FridgeSearchOptionsRequest extends FormRequest
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
            'locale' => ['sometimes', 'string', Rule::in(array_keys(config('app.available_locales')))],
        ];
    }
}
