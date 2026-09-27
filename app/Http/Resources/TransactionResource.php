<?php

namespace App\Http\Resources;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Transaction
 */
class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $categoryId = $this->budget->category_id;
        $self = route('categories.budgets.transactions.show', [$categoryId, $this->budget_id, $this->id]);

        return [
            'id' => $this->id,
            'budget_id' => $this->budget_id,
            'category_id' => $categoryId,
            'amount' => (float) $this->amount,
            'occurred_on' => $this->occurred_on->format('Y-m-d'),
            'description' => $this->description,
            'merchant' => $this->merchant,
            'payment_method' => $this->payment_method,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            '_links' => [
                'self' => ['href' => $self, 'method' => 'GET'],
                'update' => ['href' => $self, 'method' => 'PUT'],
                'delete' => ['href' => $self, 'method' => 'DELETE'],
                'budget' => ['href' => route('categories.budgets.show', [$categoryId, $this->budget_id]), 'method' => 'GET'],
                'category' => ['href' => route('categories.show', $categoryId), 'method' => 'GET'],
                'collection' => ['href' => route('categories.budgets.transactions.index', [$categoryId, $this->budget_id]), 'method' => 'GET'],
            ],
        ];
    }
}
