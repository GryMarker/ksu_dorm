<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationNotificationController extends Controller
{
    public function __construct(
        private readonly TwoFactorChallengeService $twoFactorChallengeService,
    ) {}

    /**
     * Send a new email verification code.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->session()->put(
            TwoFactorChallengeService::SESSION_KEY,
            $this->twoFactorChallengeService->begin($user, false, 'dashboard', true)
        );

        Auth::guard('web')->logout();

        return redirect()->route('two-factor.challenge')->with(
            'status',
            'A new verification code was sent to '.$user->email.'.'
        );
    }
}
