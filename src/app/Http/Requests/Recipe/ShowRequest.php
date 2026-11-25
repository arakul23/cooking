<?php

declare(strict_types=1);

namespace App\Http\Requests\Recipe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowRequest extends FormRequest
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
            'visitor_id' => ['sometimes', 'uuid'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        $data = parent::validationData();

        if ($visitorId = $this->header('X-Visitor-Id')) {
            $data['visitor_id'] = $visitorId;
        }

        return $data;
    }
}
