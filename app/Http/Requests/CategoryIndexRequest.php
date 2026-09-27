<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the filtering, sorting and pagination query parameters of GET /categories.
 */
class CategoryIndexRequest extends FormRequest
{
    public const SORTABLE = ['name', '-name', 'created_at', '-created_at'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::enum(CategoryType::class)],
            'search' => ['sometimes', 'string', 'max:100'],
            'sort' => ['sometimes', Rule::in(self::SORTABLE)],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
