<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CategoryType;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Budget;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /dashboard?month=YYYY-MM — the logged-in user's monthly overview composed from
     * the user, categories, budgets and transactions.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['sometimes', 'date_format:Y-m'],
        ]);

        $month = $validated['month'] ?? now()->format('Y-m');
        $monthStart = CarbonImmutable::createFromFormat('!Y-m', $month);
        $user = $request->user();
        $ownedByUser = fn ($query) => $query->where('user_id', $user->id);

        $budgets = Budget::query()
            ->whereHas('category', $ownedByUser)
            ->with('category')
            ->withSpent()
            ->where('month', $month)
            ->get()
            ->sortBy(fn (Budget $budget) => [$budget->category->type->value, $budget->category->name])
            ->values();

        $totalsByType = fn (CategoryType $type): array => [
            'budgeted' => round((float) $budgets->filter(fn (Budget $b) => $b->category->type === $type)->sum('amount'), 2),
            'actual' => round($budgets->filter(fn (Budget $b) => $b->category->type === $type)->sum(fn (Budget $b) => $b->spentAmount()), 2),
        ];

        $income = $totalsByType(CategoryType::Income);
        $expenses = $totalsByType(CategoryType::Expense);

        $recentTransactions = Transaction::query()
            ->with('budget')
            ->whereHas('budget', fn ($query) => $query->where('month', $month)->whereHas('category', $ownedByUser))
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'month' => $month,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ],
                'summary' => [
                    'income' => $income,
                    'expenses' => [
                        ...$expenses,
                        'remaining' => round($expenses['budgeted'] - $expenses['actual'], 2),
                    ],
                    'net_balance' => round($income['actual'] - $expenses['actual'], 2),
                    'categories_count' => $user->categories()->count(),
                    'budgets_count' => $budgets->count(),
                    'exceeded_budgets_count' => $budgets->filter(
                        fn (Budget $b) => $b->category->type === CategoryType::Expense && $b->spentAmount() > (float) $b->amount
                    )->count(),
                ],
                'budgets' => $budgets->map(fn (Budget $budget) => [
                    'budget_id' => $budget->id,
                    'category' => [
                        'id' => $budget->category->id,
                        'name' => $budget->category->name,
                        'type' => $budget->category->type,
                        'color' => $budget->category->color,
                    ],
                    'amount' => (float) $budget->amount,
                    'spent' => $budget->spentAmount(),
                    'remaining' => round((float) $budget->amount - $budget->spentAmount(), 2),
                    'usage_percent' => round($budget->spentAmount() / (float) $budget->amount * 100, 1),
                    '_links' => [
                        'budget' => ['href' => route('categories.budgets.show', [$budget->category_id, $budget->id]), 'method' => 'GET'],
                        'transactions' => ['href' => route('categories.budgets.transactions.index', [$budget->category_id, $budget->id]), 'method' => 'GET'],
                    ],
                ]),
                'recent_transactions' => TransactionResource::collection($recentTransactions),
            ],
            '_links' => [
                'self' => ['href' => route('dashboard', ['month' => $month]), 'method' => 'GET'],
                'previous_month' => ['href' => route('dashboard', ['month' => $monthStart->subMonth()->format('Y-m')]), 'method' => 'GET'],
                'next_month' => ['href' => route('dashboard', ['month' => $monthStart->addMonth()->format('Y-m')]), 'method' => 'GET'],
                'categories' => ['href' => route('categories.index'), 'method' => 'GET'],
            ],
        ]);
    }
}
