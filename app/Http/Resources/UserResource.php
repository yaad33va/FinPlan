<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Without a logged-in user (register/login responses) the user is shown to themselves.
        $viewer = $request->user() ?? $this->resource;
        $links = [];

        if ($viewer->is($this->resource)) {
            $links['self'] = ['href' => route('auth.me'), 'method' => 'GET'];
            $links['categories'] = ['href' => route('categories.index'), 'method' => 'GET'];
            $links['dashboard'] = ['href' => route('dashboard'), 'method' => 'GET'];
        }

        if ($viewer->isAdmin()) {
            $links['admin_view'] = ['href' => route('users.show', $this->id), 'method' => 'GET'];
            $links['user_categories'] = ['href' => route('categories.index', ['user_id' => $this->id]), 'method' => 'GET'];

            if (! $viewer->is($this->resource) && ! $this->isAdmin()) {
                $links['delete'] = ['href' => route('users.show', $this->id), 'method' => 'DELETE'];
            }

            $links['collection'] = ['href' => route('users.index'), 'method' => 'GET'];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'two_factor_enabled' => $this->hasTwoFactorEnabled(),
            'categories_count' => $this->whenCounted('categories'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            '_links' => $links,
        ];
    }
}
