<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PasswordResetCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetCodeController extends Controller
{
    public function __construct(
        private readonly PasswordResetCodeService $passwordResetCodeService,
    ) {}

    /**
     * Display the password reset code request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset code request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', $request->string('email')->toString())->first();

        if ($user) {
            $this->passwordResetCodeService->send($user);
        }

        return redirect()
            ->route('password.reset')
            ->withInput($request->only('email'))
            ->with('status', 'If that email exists, we sent a password reset code.');
    }
}
