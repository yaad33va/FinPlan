<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the filtering, sorting and pagination query parameters of GET /categories/{id}/budgets.
 */
class BudgetIndexRequest extends FormRequest
{
    public const SORTABLE = ['month', '-month', 'amount', '-amount'];

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
            'month' => ['sometimes', 'date_format:Y-m'],
            'month_from' => ['sometimes', 'date_format:Y-m'],
            'month_to' => ['sometimes', 'date_format:Y-m', Rule::when($this->filled('month_from'), 'after_or_equal:month_from')],
            'exceeded' => ['sometimes', Rule::in(['true', 'false', '1', '0'])],
            'sort' => ['sometimes', Rule::in(self::SORTABLE)],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
