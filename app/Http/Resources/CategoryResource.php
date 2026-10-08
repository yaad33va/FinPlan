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
        $viewer = $request->user();

        $links = ['self' => ['href' => $self, 'method' => 'GET']];

        if ($viewer?->can('update', $this->resource)) {
            $links['update'] = ['href' => $self, 'method' => 'PUT'];
        }

        if ($viewer?->can('delete', $this->resource)) {
            $links['delete'] = ['href' => $self, 'method' => 'DELETE'];
        }

        $links['budgets'] = ['href' => route('categories.budgets.index', $this->id), 'method' => 'GET'];

        if ($viewer?->can('update', $this->resource)) {
            $links['create_budget'] = ['href' => route('categories.budgets.store', $this->id), 'method' => 'POST'];
        }

        $links['transactions'] = ['href' => route('categories.transactions.index', $this->id), 'method' => 'GET'];
        $links['collection'] = ['href' => route('categories.index'), 'method' => 'GET'];

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'owner' => $this->whenLoaded('user', fn () => ['id' => $this->user->id, 'name' => $this->user->name]),
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'color' => $this->color,
            'budgets_count' => $this->whenCounted('budgets'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            '_links' => $links,
        ];
    }
}
