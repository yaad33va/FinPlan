<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryIndexRequest;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CategoryController extends Controller
{
    /**
     * GET /categories — paginated list with filtering (type, search) and sorting.
     * A member sees only their own categories; an admin sees everyone's (or one user's via user_id).
     */
    public function index(CategoryIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $sort = $filters['sort'] ?? 'name';
        $user = $request->user();

        if (! $user->isAdmin() && isset($filters['user_id']) && (int) $filters['user_id'] !== $user->id) {
            throw new AccessDeniedHttpException('Only administrators can list other users\' categories.');
        }

        $categories = Category::query()
            ->withCount('budgets')
            ->when($user->isAdmin(), fn ($query) => $query->with('user'))
            ->when(
                $user->isAdmin(),
                fn ($query) => $query->when($filters['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId)),
                fn ($query) => $query->where('user_id', $user->id),
            )
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy(ltrim($sort, '-'), str_starts_with($sort, '-') ? 'desc' : 'asc')
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        return CategoryResource::collection($categories)->additional([
            '_links' => [
                'create' => ['href' => route('categories.store'), 'method' => 'POST'],
                'dashboard' => ['href' => route('dashboard'), 'method' => 'GET'],
            ],
        ]);
    }

    /**
     * POST /categories
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = $request->user()->categories()->create($request->payload());

        return (new CategoryResource($category->loadCount('budgets')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('categories.show', $category));
    }

    /**
     * GET /categories/{category}
     */
    public function show(Category $category): CategoryResource
    {
        Gate::authorize('view', $category);

        return new CategoryResource($category->loadCount('budgets'));
    }

    /**
     * PUT|PATCH /categories/{category}
     */
    public function update(CategoryRequest $request, Category $category): CategoryResource
    {
        Gate::authorize('update', $category);

        $category->update($request->payload());

        return new CategoryResource($category->loadCount('budgets'));
    }

    /**
     * DELETE /categories/{category} — also deletes its budgets and their transactions (FK cascade).
     */
    public function destroy(Category $category): Response
    {
        Gate::authorize('delete', $category);

        $category->delete();

        return response()->noContent();
    }
}
