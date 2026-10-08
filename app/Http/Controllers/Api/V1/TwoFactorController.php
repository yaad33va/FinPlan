<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\InvalidTokenException;
use App\Http\Controllers\Api\V1\Concerns\RespondsWithTokens;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\JwtService;
use App\Services\RefreshTokenService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Two-factor authentication with time-based one-time passwords (TOTP, RFC 6238), compatible
 * with Google Authenticator, Microsoft Authenticator, Authy, etc.
 */
class TwoFactorController extends Controller
{
    use RespondsWithTokens;

    public function __construct(
        protected Google2FA $google2fa,
        protected JwtService $jwt,
        protected RefreshTokenService $refreshTokens,
    ) {}

    /**
     * POST /auth/two-factor — generates a new secret. 2FA is enabled only after it is confirmed.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            throw new ConflictHttpException('Two-factor authentication is already enabled.');
        }

        $secret = $this->google2fa->generateSecretKey();

        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => null])->save();

        return response()->json([
            'secret' => $secret,
            'otpauth_url' => $this->google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret),
            'message' => 'Scan the QR code with your authenticator app and confirm with a code.',
            '_links' => [
                'confirm' => ['href' => route('auth.two-factor.confirm'), 'method' => 'POST'],
            ],
        ]);
    }

    /**
     * POST /auth/two-factor/confirm — enables 2FA once the user proves the app works.
     */
    public function confirm(Request $request): UserResource
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);

        /** @var User $user */
        $user = $request->user();

        if ($user->two_factor_secret === null) {
            throw new ConflictHttpException('Start the two-factor setup first (POST /auth/two-factor).');
        }

        if ($user->hasTwoFactorEnabled()) {
            throw new ConflictHttpException('Two-factor authentication is already enabled.');
        }

        $this->ensureValidCode($user, $validated['code']);

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return new UserResource($user);
    }

    /**
     * DELETE /auth/two-factor — disables 2FA. The password is required, so a stolen access
     * token alone cannot turn 2FA off.
     */
    public function destroy(Request $request): Response
    {
        $validated = $request->validate(['password' => ['required', 'string']]);

        /** @var User $user */
        $user = $request->user();

        if ($user->two_factor_secret === null) {
            throw new ConflictHttpException('Two-factor authentication is not enabled.');
        }

        if (! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages(['password' => 'The password is incorrect.']);
        }

        $user->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null])->save();

        return response()->noContent();
    }

    /**
     * POST /auth/two-factor/challenge — second login step: the token from /auth/login + a TOTP code.
     *
     * @throws AuthenticationException
     */
    public function challenge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'two_factor_token' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ]);

        try {
            $claims = $this->jwt->decode($validated['two_factor_token'], JwtService::TYPE_TWO_FACTOR);
        } catch (InvalidTokenException $exception) {
            throw new AuthenticationException('The two-factor login token is invalid or has expired. Log in again.');
        }

        $user = User::find($claims->sub);

        if ($user === null || ! $user->hasTwoFactorEnabled()) {
            throw new AuthenticationException('The two-factor login token is invalid or has expired. Log in again.');
        }

        $this->ensureValidCode($user, $validated['code']);

        return $this->tokenResponse($this->refreshTokens->issue($user, $request));
    }

    /**
     * @throws ValidationException
     */
    protected function ensureValidCode(User $user, string $code): void
    {
        if (! $this->google2fa->verifyKey($user->two_factor_secret, $code)) {
            throw ValidationException::withMessages(['code' => 'The two-factor code is invalid.']);
        }
    }
}
