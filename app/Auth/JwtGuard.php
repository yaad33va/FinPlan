<?php

namespace App\Auth;

use App\Exceptions\InvalidTokenException;
use App\Services\JwtService;
use App\Services\RefreshTokenService;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

/**
 * The "api" guard (route middleware auth:api). A request is authenticated when its
 * "Authorization: Bearer <token>" header holds an access token that:
 *
 * 1. has a valid HS256 signature and is not expired (JwtService::decode),
 * 2. belongs to a login session that has not been revoked by logout,
 * 3. points to a user that still exists.
 *
 * The verified claims are stored in the "jwt" request attribute (used e.g. by the role middleware).
 */
class JwtGuard implements Guard
{
    use GuardHelpers;

    protected bool $resolved = false;

    public function __construct(
        UserProvider $provider,
        protected Request $request,
        protected JwtService $jwt,
        protected RefreshTokenService $refreshTokens,
    ) {
        $this->provider = $provider;
    }

    public function user(): ?Authenticatable
    {
        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;

        return $this->user = $this->authenticate();
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function validate(array $credentials = []): bool
    {
        return false;
    }

    /**
     * Called for every new request, so a user resolved for a previous request is never reused.
     */
    public function setRequest(Request $request): static
    {
        $this->request = $request;
        $this->user = null;
        $this->resolved = false;

        return $this;
    }

    protected function authenticate(): ?Authenticatable
    {
        $token = $this->request->bearerToken();

        if ($token === null || $token === '') {
            return null;
        }

        try {
            $claims = $this->jwt->decode($token, JwtService::TYPE_ACCESS);
        } catch (InvalidTokenException $exception) {
            $this->request->attributes->set('auth_error', $exception->getMessage());

            return null;
        }

        if (! $this->refreshTokens->isSessionActive($claims->sid)) {
            $this->request->attributes->set('auth_error', 'The session has ended (logged out). Please log in again.');

            return null;
        }

        $this->request->attributes->set('jwt', $claims);

        return $this->provider->retrieveById($claims->sub);
    }
}
