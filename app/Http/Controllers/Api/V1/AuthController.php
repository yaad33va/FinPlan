<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithTokens;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\JwtService;
use App\Services\RefreshTokenService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use RespondsWithTokens;

    public function __construct(
        protected JwtService $jwt,
        protected RefreshTokenService $refreshTokens,
    ) {}

    /**
     * POST /auth/register — creates a member account and logs it in.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = new User($request->safe()->only(['name', 'email', 'password']));
        $user->role = UserRole::Member;
        $user->save();

        return $this->tokenResponse($this->refreshTokens->issue($user, $request), Response::HTTP_CREATED)
            ->header('Location', route('auth.me'));
    }

    /**
     * POST /auth/login — checks the password; returns tokens, or a 2FA challenge when 2FA is on.
     *
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if ($user === null || ! Hash::check($request->validated('password'), $user->password)) {
            throw new AuthenticationException('Invalid email or password.');
        }

        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'two_factor_required' => true,
                'two_factor_token' => $this->jwt->issueTwoFactorToken($user),
                'expires_in' => config('jwt.two_factor_ttl') * 60,
                'message' => 'Enter the 6-digit code from your authenticator app.',
                '_links' => [
                    'challenge' => ['href' => route('auth.two-factor.challenge'), 'method' => 'POST'],
                ],
            ]);
        }

        return $this->tokenResponse($this->refreshTokens->issue($user, $request));
    }

    /**
     * POST /auth/refresh — exchanges the refresh token cookie for a new access token (rotation).
     */
    public function refresh(Request $request): JsonResponse
    {
        $plainToken = (string) $request->cookie(config('jwt.cookie.name'));

        try {
            if ($plainToken === '') {
                throw new AuthenticationException('The refresh token cookie is missing. Please log in.');
            }

            $tokens = $this->refreshTokens->rotate($plainToken, $request);
        } catch (AuthenticationException $exception) {
            return response()
                ->json(['message' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED)
                ->withCookie($this->forgetRefreshCookie());
        }

        return $this->tokenResponse($tokens);
    }

    /**
     * POST /auth/logout — revokes the session: its refresh tokens and its access tokens stop working.
     */
    public function logout(Request $request): Response
    {
        $this->refreshTokens->revokeSession($request->attributes->get('jwt')->sid);

        return response()->noContent()->withCookie($this->forgetRefreshCookie());
    }

    /**
     * GET /auth/me — the logged-in user's profile.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->loadCount('categories'));
    }
}
