<?php

namespace App\Http\Resources;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Budget
 */
class BudgetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $amount = (float) $this->amount;
        $spent = $this->spentAmount();
        $self = route('categories.budgets.show', [$this->category_id, $this->id]);

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'month' => $this->month,
            'amount' => $amount,
            'spent' => $spent,
            'remaining' => round($amount - $spent, 2),
            'usage_percent' => round($spent / $amount * 100, 1),
            'is_exceeded' => $spent > $amount,
            'note' => $this->note,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            '_links' => [
                'self' => ['href' => $self, 'method' => 'GET'],
                'update' => ['href' => $self, 'method' => 'PUT'],
                'delete' => ['href' => $self, 'method' => 'DELETE'],
                'category' => ['href' => route('categories.show', $this->category_id), 'method' => 'GET'],
                'transactions' => ['href' => route('categories.budgets.transactions.index', [$this->category_id, $this->id]), 'method' => 'GET'],
                'create_transaction' => ['href' => route('categories.budgets.transactions.store', [$this->category_id, $this->id]), 'method' => 'POST'],
                'collection' => ['href' => route('categories.budgets.index', $this->category_id), 'method' => 'GET'],
            ],
        ];
    }
}
