<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'amount',
        'occurred_on',
        'description',
        'merchant',
        'payment_method',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_on' => 'date',
            'payment_method' => PaymentMethod::class,
        ];
    }

    /**
     * @return BelongsTo<Budget, $this>
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Applies the validated list filters and sorting (see TransactionIndexRequest).
     *
     * @param  Builder<Transaction>  $query
     * @param  array{date_from?: string, date_to?: string, min_amount?: numeric-string, max_amount?: numeric-string, payment_method?: string, search?: string, sort?: string}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('occurred_on', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('occurred_on', '<=', $date))
            ->when(isset($filters['min_amount']), fn (Builder $q) => $q->where('transactions.amount', '>=', $filters['min_amount']))
            ->when(isset($filters['max_amount']), fn (Builder $q) => $q->where('transactions.amount', '<=', $filters['max_amount']))
            ->when($filters['payment_method'] ?? null, fn (Builder $q, string $method) => $q->where('payment_method', $method))
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(
                fn (Builder $inner) => $inner
                    ->where('description', 'like', "%{$search}%")
                    ->orWhere('merchant', 'like', "%{$search}%")
            ));

        $sort = $filters['sort'] ?? '-occurred_on';

        $query
            ->orderBy('transactions.'.ltrim($sort, '-'), str_starts_with($sort, '-') ? 'desc' : 'asc')
            ->orderBy('transactions.id', 'desc');
    }
}
