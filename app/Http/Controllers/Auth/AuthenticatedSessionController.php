<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\TwoFactorChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private const TENANT_TRUSTED_DEVICE_COOKIE = 'tenant_trusted_device';

    private const TENANT_TRUSTED_DEVICE_DAYS = 90;

    public function __construct(
        private readonly TwoFactorChallengeService $twoFactorChallengeService,
    ) {}

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $request->authenticate();

        if ($user->isDormMaster()) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        }

        if ($user->isTenant() && $this->hasValidTrustedTenantDevice($request, $user)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        }

        $challenge = $this->twoFactorChallengeService->begin($user, $request->boolean('remember'), 'dashboard');

        $request->session()->put(TwoFactorChallengeService::SESSION_KEY, $challenge);

        return redirect()->route('two-factor.challenge')->with(
            $challenge['mail_sent'] ? 'status' : 'error',
            $challenge['mail_sent']
                ? 'We sent a one-time login code to '.$user->email.'.'
                : 'We could not send the login code. Check the mail settings, then request a new code.'
        );
    }

    /**
     * Display the two-factor challenge view.
     */
    public function createTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        $challenge = $request->session()->get(TwoFactorChallengeService::SESSION_KEY);

        if (! is_array($challenge) || empty($challenge['attempt_id']) || ! $this->twoFactorChallengeService->hasActiveChallenge($challenge['attempt_id'])) {
            $request->session()->forget(TwoFactorChallengeService::SESSION_KEY);

            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge', [
            'email' => $challenge['email'] ?? '',
            'expiresInMinutes' => TwoFactorChallengeService::TTL_MINUTES,
        ]);
    }

    /**
     * Complete the two-factor challenge and authenticate the user.
     */
    public function storeTwoFactorChallenge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $challenge = $request->session()->get(TwoFactorChallengeService::SESSION_KEY);

        if (! is_array($challenge) || empty($challenge['attempt_id'])) {
            throw ValidationException::withMessages([
                'code' => 'Your login session expired. Please sign in again.',
            ]);
        }

        $payload = $this->twoFactorChallengeService->getPayload($challenge['attempt_id']);

        if (! $payload) {
            $request->session()->forget(TwoFactorChallengeService::SESSION_KEY);

            throw ValidationException::withMessages([
                'code' => 'That code expired. Please sign in again to get a new one.',
            ]);
        }

        if (! hash_equals($payload['code_hash'], hash('sha256', $validated['code']))) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid.',
            ]);
        }

        $user = User::query()->find($payload['user_id']);

        if (! $user) {
            $this->twoFactorChallengeService->consume($challenge['attempt_id']);
            $request->session()->forget(TwoFactorChallengeService::SESSION_KEY);

            throw ValidationException::withMessages([
                'code' => 'Unable to complete sign in. Please try again.',
            ]);
        }

        $this->twoFactorChallengeService->consume($challenge['attempt_id']);
        $request->session()->forget(TwoFactorChallengeService::SESSION_KEY);

        Auth::login($user, (bool) $payload['remember']);

        if (($payload['mark_email_as_verified'] ?? false) && ! $user->hasVerifiedEmail()) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        if ($user->isTenant()) {
            $this->queueTrustedTenantDeviceCookie($request, $user);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($payload['redirect_route'] ?? 'dashboard', absolute: false));
    }

    /**
     * Resend the current two-factor code.
     */
    public function resendTwoFactorChallenge(Request $request): RedirectResponse
    {
        $challenge = $request->session()->get(TwoFactorChallengeService::SESSION_KEY);

        if (! is_array($challenge) || empty($challenge['attempt_id'])) {
            return redirect()->route('login');
        }

        $payload = $this->twoFactorChallengeService->getPayload($challenge['attempt_id']);

        if (! $payload) {
            $request->session()->forget(TwoFactorChallengeService::SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'email' => 'Your login session expired. Please sign in again.',
            ]);
        }

        $user = User::query()->find($payload['user_id']);

        if (! $user) {
            $this->twoFactorChallengeService->consume($challenge['attempt_id']);
            $request->session()->forget(TwoFactorChallengeService::SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'email' => 'Unable to resend the code. Please sign in again.',
            ]);
        }

        $mailSent = $this->twoFactorChallengeService->resend(
            $user,
            $challenge['attempt_id'],
            (bool) $payload['remember'],
            $payload['redirect_route'] ?? 'dashboard',
            (bool) ($payload['mark_email_as_verified'] ?? false)
        );

        return back()->with(
            $mailSent ? 'status' : 'error',
            $mailSent
                ? 'A new verification code was sent to '.$user->email.'.'
                : 'We could not send a new verification code. Check the mail settings and try again.'
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function hasValidTrustedTenantDevice(Request $request, User $user): bool
    {
        $rawCookie = $request->cookie(self::TENANT_TRUSTED_DEVICE_COOKIE);

        if (! is_string($rawCookie) || $rawCookie === '') {
            return false;
        }

        $payload = json_decode($rawCookie, true);

        if (! is_array($payload)) {
            return false;
        }

        if (($payload['user_id'] ?? null) !== $user->id) {
            return false;
        }

        if (($payload['user_agent_hash'] ?? null) !== $this->tenantDeviceUserAgentHash($request)) {
            return false;
        }

        return isset($payload['expires_at']) && now()->lt(Carbon::parse($payload['expires_at']));
    }

    private function queueTrustedTenantDeviceCookie(Request $request, User $user): void
    {
        Cookie::queue(cookie(
            self::TENANT_TRUSTED_DEVICE_COOKIE,
            json_encode([
                'user_id' => $user->id,
                'user_agent_hash' => $this->tenantDeviceUserAgentHash($request),
                'expires_at' => now()->addDays(self::TENANT_TRUSTED_DEVICE_DAYS)->toIso8601String(),
            ], JSON_THROW_ON_ERROR),
            self::TENANT_TRUSTED_DEVICE_DAYS * 24 * 60
        ));
    }

    private function tenantDeviceUserAgentHash(Request $request): string
    {
        return hash('sha256', $request->userAgent() ?? 'unknown-device');
    }
}
