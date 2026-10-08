<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonInterface;

/**
 * The tokens handed out after login or refresh.
 */
final readonly class TokenPair
{
    public function __construct(
        public User $user,
        public string $accessToken,
        public int $expiresIn,
        public string $refreshToken,
        public CarbonInterface $refreshExpiresAt,
    ) {}
}
