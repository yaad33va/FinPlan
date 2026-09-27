<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates POST (create), PUT (full replace) and PATCH (partial update) of a category.
 */
class CategoryRequest extends FormRequest
{
    /**
     * Optional attributes that PUT resets to null when they are omitted.
     *
     * @var list<string>
     */
    protected const NULLABLE_ATTRIBUTES = ['description', 'color'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $presence = $this->isMethod('PATCH') ? 'sometimes' : 'required';

        return [
            'name' => [$presence, 'string', 'min:2', 'max:100'],
            'type' => [$presence, Rule::enum(CategoryType::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'color.regex' => 'The color must be a hex color code, e.g. #FF8800.',
        ];
    }

    /**
     * The attributes to persist. PUT replaces the whole resource, so omitted optional
     * attributes are cleared; PATCH changes only the attributes that were sent.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        if ($this->isMethod('PUT')) {
            return array_merge(array_fill_keys(self::NULLABLE_ATTRIBUTES, null), $this->validated());
        }

        return $this->validated();
    }
}
