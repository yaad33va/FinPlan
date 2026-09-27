<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates POST (create), PUT (full replace) and PATCH (partial update) of a transaction.
 */
class TransactionRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    protected const NULLABLE_ATTRIBUTES = ['merchant'];

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
            'amount' => [$presence, 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'occurred_on' => [$presence, 'date_format:Y-m-d'],
            'description' => [$presence, 'string', 'min:2', 'max:255'],
            'merchant' => ['nullable', 'string', 'max:100'],
            'payment_method' => [$presence, Rule::enum(PaymentMethod::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'occurred_on.date_format' => 'The occurred on date must be in YYYY-MM-DD format.',
        ];
    }

    /**
     * The transaction date must fall inside the month of the parent budget.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('occurred_on')) {
                    return;
                }

                /** @var Budget $budget */
                $budget = $this->route('budget');
                /** @var Transaction|null $transaction */
                $transaction = $this->route('transaction');

                $date = $this->input('occurred_on') ?? $transaction?->occurred_on?->format('Y-m-d');

                if ($date !== null && ! str_starts_with($date, $budget->month.'-')) {
                    $validator->errors()->add('occurred_on', "The occurred on date must be within the budget month ({$budget->month}).");
                }
            },
        ];
    }

    /**
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
