<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FR-001 validation. The same limits are mirrored client-side in
 * resources/js/lib/validation.js - keep both in sync.
 */
class StoreServiceRequest extends FormRequest
{
    public const DESCRIPTION_MIN = 10;
    public const DESCRIPTION_MAX = 2000;
    public const TEXT_MAX = 255;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'location' => ['required', 'string', 'max:'.self::TEXT_MAX],
            'area' => ['required', 'string', 'max:'.self::TEXT_MAX],
            'description' => ['required', 'string', 'min:'.self::DESCRIPTION_MIN, 'max:'.self::DESCRIPTION_MAX],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Choose a category.',
            'category_id.exists' => 'Choose a valid category.',
            'location.required' => 'Enter the location.',
            'area.required' => 'Enter the area.',
            'description.required' => 'Describe the problem.',
            'description.min' => 'Description must be at least '.self::DESCRIPTION_MIN.' characters.',
            'description.max' => 'Description must be at most '.self::DESCRIPTION_MAX.' characters.',
        ];
    }
}
