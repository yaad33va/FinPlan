<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A refresh token of one login session. Only the SHA-256 hash of the token is stored,
 * so a database leak does not reveal usable tokens.
 */
class RefreshToken extends Model
{
    use Prunable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'session_id',
        'token_hash',
        'expires_at',
        'revoked_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Expired or revoked tokens are deleted by "php artisan model:prune" (scheduled daily).
     *
     * @return Builder<RefreshToken>
     */
    public function prunable(): Builder
    {
        return static::query()
            ->where('expires_at', '<', now()->subDay())
            ->orWhere('revoked_at', '<', now()->subDay());
    }

    /**
     * Tokens that are not revoked and not expired.
     *
     * @param  Builder<RefreshToken>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('revoked_at')->where('expires_at', '>', now());
    }
}
