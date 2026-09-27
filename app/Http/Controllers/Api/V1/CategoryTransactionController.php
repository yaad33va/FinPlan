<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionIndexRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryTransactionController extends Controller
{
    /**
     * GET /categories/{category}/transactions — all transactions of a category across all of its budgets.
     */
    public function __invoke(TransactionIndexRequest $request, Category $category): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $transactions = $category->transactions()
            ->with('budget')
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        return TransactionResource::collection($transactions)->additional([
            '_links' => [
                'category' => ['href' => route('categories.show', $category), 'method' => 'GET'],
                'budgets' => ['href' => route('categories.budgets.index', $category), 'method' => 'GET'],
            ],
        ]);
    }
}
