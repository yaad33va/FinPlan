<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserIndexRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * User management, available only to the admin role (see the "role:admin" route middleware).
 */
class UserController extends Controller
{
    /**
     * GET /users — paginated list with search and role filter.
     */
    public function index(UserIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $sort = $filters['sort'] ?? 'name';

        $users = User::query()
            ->withCount('categories')
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(
                fn ($inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")
            ))
            ->orderBy(ltrim($sort, '-'), str_starts_with($sort, '-') ? 'desc' : 'asc')
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        return UserResource::collection($users);
    }

    /**
     * GET /users/{user}
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user->loadCount('categories'));
    }

    /**
     * DELETE /users/{user} — deletes a member together with all of their data (FK cascade).
     */
    public function destroy(Request $request, User $user): Response
    {
        if ($user->is($request->user())) {
            throw new AccessDeniedHttpException('You cannot delete your own account.');
        }

        if ($user->isAdmin()) {
            throw new AccessDeniedHttpException('Administrator accounts cannot be deleted.');
        }

        $user->delete();

        return response()->noContent();
    }
}
