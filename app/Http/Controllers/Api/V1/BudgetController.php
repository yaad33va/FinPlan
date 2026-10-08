<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetIndexRequest;
use App\Http\Requests\BudgetRequest;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class BudgetController extends Controller
{
    /**
     * GET /categories/{category}/budgets — paginated list with month range / exceeded filters.
     */
    public function index(BudgetIndexRequest $request, Category $category): AnonymousResourceCollection
    {
        Gate::authorize('view', $category);

        $filters = $request->validated();
        $sort = $filters['sort'] ?? '-month';

        $spentSubquery = Transaction::query()
            ->selectRaw('COALESCE(SUM(amount), 0)')
            ->whereColumn('transactions.budget_id', 'budgets.id');

        $budgets = $category->budgets()
            ->withSpent()
            ->when($filters['month'] ?? null, fn ($query, string $month) => $query->where('month', $month))
            ->when($filters['month_from'] ?? null, fn ($query, string $month) => $query->where('month', '>=', $month))
            ->when($filters['month_to'] ?? null, fn ($query, string $month) => $query->where('month', '<=', $month))
            ->when($request->has('exceeded'), fn ($query) => $query->where(
                'budgets.amount',
                $request->boolean('exceeded') ? '<' : '>=',
                $spentSubquery,
            ))
            ->orderBy(ltrim($sort, '-'), str_starts_with($sort, '-') ? 'desc' : 'asc')
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        return BudgetResource::collection($budgets)->additional([
            '_links' => [
                'category' => ['href' => route('categories.show', $category), 'method' => 'GET'],
                'create' => ['href' => route('categories.budgets.store', $category), 'method' => 'POST'],
            ],
        ]);
    }

    /**
     * POST /categories/{category}/budgets
     */
    public function store(BudgetRequest $request, Category $category): JsonResponse
    {
        Gate::authorize('update', $category);

        $budget = $category->budgets()->create($request->payload());

        return (new BudgetResource($budget))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('categories.budgets.show', [$category, $budget]));
    }

    /**
     * GET /categories/{category}/budgets/{budget}
     */
    public function show(Category $category, Budget $budget): BudgetResource
    {
        Gate::authorize('view', $category);

        return new BudgetResource($budget);
    }

    /**
     * PUT|PATCH /categories/{category}/budgets/{budget}
     */
    public function update(BudgetRequest $request, Category $category, Budget $budget): BudgetResource
    {
        Gate::authorize('update', $category);

        $budget->update($request->payload());

        return new BudgetResource($budget);
    }

    /**
     * DELETE /categories/{category}/budgets/{budget} — also deletes its transactions (FK cascade).
     */
    public function destroy(Category $category, Budget $budget): Response
    {
        Gate::authorize('delete', $category);

        $budget->delete();

        return response()->noContent();
    }
}
