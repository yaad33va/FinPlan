<?php

namespace App\Services;

use App\Exceptions\InvalidTokenException;
use App\Models\User;
use DomainException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

/**
 * Creates and verifies the JSON Web Tokens of the API.
 *
 * A token is "header.payload.signature" (each part base64url encoded). The payload is
 * readable by anyone, so it holds no secrets; the HS256 signature (HMAC-SHA256 with
 * JWT_SECRET) guarantees that nobody can change the claims (e.g. role) without the secret.
 */
class JwtService
{
    public const TYPE_ACCESS = 'access';

    public const TYPE_TWO_FACTOR = '2fa';

    /**
     * Short-lived access token sent as "Authorization: Bearer <token>".
     *
     * Claims: iss, sub (user ID), iat, nbf, exp, jti (unique token ID), typ, sid (login
     * session, revoked on logout), role and name (so the client can adapt the UI).
     */
    public function issueAccessToken(User $user, string $sessionId): string
    {
        return $this->encode([
            'sub' => (string) $user->id,
            'typ' => self::TYPE_ACCESS,
            'sid' => $sessionId,
            'role' => $user->role->value,
            'name' => $user->name,
        ], config('jwt.access_ttl') * 60);
    }

    /**
     * Token proving that the password was correct; exchanged for real tokens together
     * with a valid 2FA code. It cannot be used to call the API.
     */
    public function issueTwoFactorToken(User $user): string
    {
        return $this->encode([
            'sub' => (string) $user->id,
            'typ' => self::TYPE_TWO_FACTOR,
        ], config('jwt.two_factor_ttl') * 60);
    }

    public function accessTokenTtl(): int
    {
        return config('jwt.access_ttl') * 60;
    }

    /**
     * Verifies the signature, expiry and type of a token and returns its claims.
     *
     * @throws InvalidTokenException
     */
    public function decode(string $token, string $expectedType): object
    {
        // Use the application clock (also used by tests that travel in time).
        JWT::$timestamp = now()->getTimestamp();

        try {
            $claims = JWT::decode($token, new Key($this->secret(), config('jwt.algorithm')));
        } catch (ExpiredException) {
            throw new InvalidTokenException('The token has expired.');
        } catch (UnexpectedValueException|DomainException|InvalidArgumentException) {
            throw new InvalidTokenException('The token is invalid.');
        }

        if (($claims->typ ?? null) !== $expectedType || ! isset($claims->sub) || ($claims->iss ?? null) !== config('jwt.issuer')) {
            throw new InvalidTokenException('The token is invalid.');
        }

        if ($expectedType === self::TYPE_ACCESS && ! isset($claims->sid, $claims->role)) {
            throw new InvalidTokenException('The token is invalid.');
        }

        return $claims;
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    protected function encode(array $claims, int $ttlSeconds): string
    {
        $now = now()->getTimestamp();

        $payload = [
            'iss' => config('jwt.issuer'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttlSeconds,
            'jti' => (string) Str::uuid(),
            ...$claims,
        ];

        return JWT::encode($payload, $this->secret(), config('jwt.algorithm'));
    }

    protected function secret(): string
    {
        $secret = config('jwt.secret');

        if (! is_string($secret) || strlen($secret) < 32) {
            throw new RuntimeException('JWT_SECRET is missing or shorter than 32 characters. Run "php artisan jwt:secret".');
        }

        return $secret;
    }
}
