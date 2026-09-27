<?php

namespace App\Models;

use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'month',
        'amount',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Adds the "spent" aggregate (sum of the budget's transactions).
     *
     * @param  Builder<Budget>  $query
     */
    public function scopeWithSpent(Builder $query): void
    {
        $query->withSum('transactions as spent', 'amount');
    }

    public function spentAmount(): float
    {
        if (! array_key_exists('spent', $this->attributes)) {
            $this->loadSum('transactions as spent', 'amount');
        }

        return round((float) $this->attributes['spent'], 2);
    }
}
