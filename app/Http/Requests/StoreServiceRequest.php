<?php

namespace App\Http\Requests;

use App\Domain\Categories\CategoryHandler;
use App\Domain\Categories\CategoryRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'location' => ['required', 'string', 'max:255'],
            'area' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ];

        // D-010: the chosen category's handler adds its own rules on top of the base rules
        foreach ($this->categoryHandler()->rules() as $field => $extra) {
            $rules[$field] = [...($rules[$field] ?? []), ...$extra];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Choose a category.',
            'category_id.exists' => 'Choose a valid category.',
            'location.required' => 'Enter the location.',
            'area.required' => 'Enter the area.',
            'description.required' => 'Describe the problem.',
            ...$this->categoryHandler()->messages(),
        ];
    }

    private function categoryHandler(): CategoryHandler
    {
        return app(CategoryRegistry::class)->forCategoryId($this->input('category_id'));
    }
}
