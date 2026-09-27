<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the filtering, sorting and pagination query parameters of the transaction lists.
 */
class TransactionIndexRequest extends FormRequest
{
    public const SORTABLE = ['occurred_on', '-occurred_on', 'amount', '-amount'];

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
            'date_from' => ['sometimes', 'date_format:Y-m-d'],
            'date_to' => ['sometimes', 'date_format:Y-m-d', Rule::when($this->filled('date_from'), 'after_or_equal:date_from')],
            'min_amount' => ['sometimes', 'numeric', 'min:0'],
            'max_amount' => ['sometimes', 'numeric', 'min:0', Rule::when(is_numeric($this->query('min_amount')), 'gte:min_amount')],
            'payment_method' => ['sometimes', Rule::enum(PaymentMethod::class)],
            'search' => ['sometimes', 'string', 'max:100'],
            'sort' => ['sometimes', Rule::in(self::SORTABLE)],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
