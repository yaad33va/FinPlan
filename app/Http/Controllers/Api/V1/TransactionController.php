<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionIndexRequest;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TransactionController extends Controller
{
    /**
     * GET /categories/{category}/budgets/{budget}/transactions
     */
    public function index(TransactionIndexRequest $request, Category $category, Budget $budget): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $transactions = $budget->transactions()
            ->filter($filters)
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        $transactions->getCollection()->each->setRelation('budget', $budget);

        return TransactionResource::collection($transactions)->additional([
            '_links' => [
                'budget' => ['href' => route('categories.budgets.show', [$category, $budget]), 'method' => 'GET'],
                'category' => ['href' => route('categories.show', $category), 'method' => 'GET'],
                'create' => ['href' => route('categories.budgets.transactions.store', [$category, $budget]), 'method' => 'POST'],
            ],
        ]);
    }

    /**
     * POST /categories/{category}/budgets/{budget}/transactions
     */
    public function store(TransactionRequest $request, Category $category, Budget $budget): JsonResponse
    {
        $transaction = $budget->transactions()->create($request->payload());
        $transaction->setRelation('budget', $budget);

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('categories.budgets.transactions.show', [$category, $budget, $transaction]));
    }

    /**
     * GET /categories/{category}/budgets/{budget}/transactions/{transaction}
     */
    public function show(Category $category, Budget $budget, Transaction $transaction): TransactionResource
    {
        return new TransactionResource($transaction->setRelation('budget', $budget));
    }

    /**
     * PUT|PATCH /categories/{category}/budgets/{budget}/transactions/{transaction}
     */
    public function update(TransactionRequest $request, Category $category, Budget $budget, Transaction $transaction): TransactionResource
    {
        $transaction->update($request->payload());

        return new TransactionResource($transaction->setRelation('budget', $budget));
    }

    /**
     * DELETE /categories/{category}/budgets/{budget}/transactions/{transaction}
     */
    public function destroy(Category $category, Budget $budget, Transaction $transaction): Response
    {
        $transaction->delete();

        return response()->noContent();
    }
}
