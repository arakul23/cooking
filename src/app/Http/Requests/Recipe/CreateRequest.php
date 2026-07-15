<?php

namespace App\Http\Requests\Recipe;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'categories' => 'required|array|min:1',
        ];
    }
}
