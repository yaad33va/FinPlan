<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $self = route('categories.show', $this->id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'color' => $this->color,
            'budgets_count' => $this->whenCounted('budgets'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            '_links' => [
                'self' => ['href' => $self, 'method' => 'GET'],
                'update' => ['href' => $self, 'method' => 'PUT'],
                'delete' => ['href' => $self, 'method' => 'DELETE'],
                'budgets' => ['href' => route('categories.budgets.index', $this->id), 'method' => 'GET'],
                'create_budget' => ['href' => route('categories.budgets.store', $this->id), 'method' => 'POST'],
                'transactions' => ['href' => route('categories.transactions.index', $this->id), 'method' => 'GET'],
                'collection' => ['href' => route('categories.index'), 'method' => 'GET'],
            ],
        ];
    }
}
