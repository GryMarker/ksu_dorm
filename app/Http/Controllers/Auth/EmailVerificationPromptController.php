<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationPromptController extends Controller
{
    public function __construct(
        private readonly TwoFactorChallengeService $twoFactorChallengeService,
    ) {}

    /**
     * Send an email verification code and show the code challenge.
     */
    public function __invoke(Request $request): RedirectResponse
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
            'We sent a verification code to '.$user->email.'.'
        );
    }
}
