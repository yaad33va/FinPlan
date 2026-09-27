<?php

namespace App\Http\Requests;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates POST (create), PUT (full replace) and PATCH (partial update) of a budget.
 */
class BudgetRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    protected const NULLABLE_ATTRIBUTES = ['note'];

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

        /** @var Category $category */
        $category = $this->route('category');
        /** @var Budget|null $budget */
        $budget = $this->route('budget');

        return [
            'month' => [
                $presence,
                'string',
                'date_format:Y-m',
                Rule::unique('budgets', 'month')
                    ->where('category_id', $category->id)
                    ->ignore($budget?->id),
            ],
            'amount' => [$presence, 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'month.date_format' => 'The month must be in YYYY-MM format, e.g. 2026-09.',
            'month.unique' => 'This category already has a budget for the given month.',
        ];
    }

    /**
     * A budget's transactions must stay inside its month, so the month of a budget that
     * already has transactions cannot be changed.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Budget|null $budget */
                $budget = $this->route('budget');

                if ($budget === null || $validator->errors()->has('month') || ! $this->has('month')) {
                    return;
                }

                if ($this->input('month') !== $budget->month && $budget->transactions()->exists()) {
                    $validator->errors()->add('month', 'The month cannot be changed because the budget already has transactions.');
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
