<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Http\Resources\UserResource;
use App\Services\TokenPair;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Token responses: the access token goes into the JSON body (the client sends it back in the
 * Authorization header), the refresh token goes into an HttpOnly cookie that JavaScript
 * cannot read and that the browser sends only to /api/v1/auth/*.
 */
trait RespondsWithTokens
{
    protected function tokenResponse(TokenPair $tokens, int $status = 200): JsonResponse
    {
        return response()
            ->json([
                'token_type' => 'Bearer',
                'access_token' => $tokens->accessToken,
                'expires_in' => $tokens->expiresIn,
                'refresh_token_expires_at' => $tokens->refreshExpiresAt->toIso8601String(),
                'data' => new UserResource($tokens->user),
            ], $status)
            ->withCookie($this->refreshCookie($tokens->refreshToken, $tokens->refreshExpiresAt->getTimestamp()));
    }

    protected function refreshCookie(string $value, int $expiresAt): Cookie
    {
        return Cookie::create(
            name: config('jwt.cookie.name'),
            value: $value,
            expire: $expiresAt,
            path: config('jwt.cookie.path'),
            secure: config('jwt.cookie.secure'),
            httpOnly: true,
            raw: false,
            sameSite: config('jwt.cookie.same_site'),
        );
    }

    protected function forgetRefreshCookie(): Cookie
    {
        return $this->refreshCookie('', 1);
    }
}
