<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Refresh token strategy:
 *
 * - Login starts a session (session_id) and returns a short-lived access JWT plus a long-lived,
 *   random refresh token. Only the refresh token's SHA-256 hash is stored in the database.
 * - POST /auth/refresh rotates the refresh token: a new one is issued and the old one expires
 *   after a short grace period, so a stolen old token soon becomes useless.
 * - Logout revokes every refresh token of the session. Access tokens carry the session ID
 *   ("sid" claim) and are rejected as soon as their session is revoked.
 */
class RefreshTokenService
{
    public function __construct(protected JwtService $jwt) {}

    /**
     * Issues an access + refresh token pair. A new session is started unless one is given.
     */
    public function issue(User $user, Request $request, ?string $sessionId = null): TokenPair
    {
        $sessionId ??= (string) Str::uuid();
        $plainToken = Str::random(80);
        $expiresAt = now()->addMinutes(config('jwt.refresh_ttl'));

        $user->refreshTokens()->create([
            'session_id' => $sessionId,
            'token_hash' => $this->hash($plainToken),
            'expires_at' => $expiresAt,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);

        return new TokenPair(
            user: $user,
            accessToken: $this->jwt->issueAccessToken($user, $sessionId),
            expiresIn: $this->jwt->accessTokenTtl(),
            refreshToken: $plainToken,
            refreshExpiresAt: $expiresAt,
        );
    }

    /**
     * Exchanges a valid refresh token for a new token pair in the same session.
     *
     * @throws AuthenticationException
     */
    public function rotate(string $plainToken, Request $request): TokenPair
    {
        $refreshToken = RefreshToken::query()
            ->active()
            ->with('user')
            ->where('token_hash', $this->hash($plainToken))
            ->first();

        if ($refreshToken === null) {
            throw new AuthenticationException('The refresh token is invalid, expired or revoked.');
        }

        $graceUntil = now()->addSeconds(config('jwt.rotation_grace'));

        if ($refreshToken->expires_at->greaterThan($graceUntil)) {
            $refreshToken->update(['expires_at' => $graceUntil]);
        }

        return $this->issue($refreshToken->user, $request, $refreshToken->session_id);
    }

    /**
     * Logout: every refresh token of the session is revoked, which also invalidates
     * the access tokens that carry this session ID.
     */
    public function revokeSession(string $sessionId): void
    {
        RefreshToken::query()
            ->where('session_id', $sessionId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    public function revokeAllForUser(User $user): void
    {
        $user->refreshTokens()->whereNull('revoked_at')->update(['revoked_at' => now()]);
    }

    public function isSessionActive(string $sessionId): bool
    {
        return RefreshToken::query()->active()->where('session_id', $sessionId)->exists();
    }

    protected function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
