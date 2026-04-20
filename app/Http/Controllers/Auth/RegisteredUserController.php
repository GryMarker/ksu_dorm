<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TwoFactorChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly TwoFactorChallengeService $twoFactorChallengeService,
    ) {}

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', Rule::in([Tenant::TYPE_STUDENT, Tenant::TYPE_EMPLOYEE])],
            'employee_id_number' => [
                'nullable',
                'string',
                'min:1',
                'max:255',
                'unique:'.Tenant::class.',employee_id_number',
                'required_if:user_type,'.Tenant::TYPE_EMPLOYEE,
            ],
        ]);

        $userType = $validated['user_type'];

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $userType === Tenant::TYPE_EMPLOYEE ? User::ROLE_EMPLOYEE : User::ROLE_TENANT,
        ]);

        $user->tenant()->create([
            'full_name' => $validated['name'],
            'sex' => null,
            'type' => $userType,
            'employee_id_number' => $userType === Tenant::TYPE_EMPLOYEE ? $validated['employee_id_number'] : null,
            'monthly_rate' => $userType === Tenant::TYPE_EMPLOYEE ? Tenant::DEFAULT_EMPLOYEE_MONTHLY_RATE : null,
            'salary_deduction' => false,
            'family_members' => [],
            'onboarding_status' => $userType === Tenant::TYPE_EMPLOYEE ? Tenant::STATUS_FOR_APPROVAL : Tenant::STATUS_DRAFT,
            'university_id_no' => 'PENDING-'.Str::upper(Str::random(6)),
            'program' => null,
            'year_level' => null,
            'phone' => '',
            'emergency_contact_name' => '',
            'emergency_contact_phone' => '',
            'medical_notes' => null,
            'admission_form_json' => [],
        ]);

        $challenge = $this->twoFactorChallengeService->begin(
            $user,
            false,
            $userType === Tenant::TYPE_EMPLOYEE ? 'employee.apply.form' : 'tenant.apply.form',
            true
        );

        $request->session()->put(TwoFactorChallengeService::SESSION_KEY, $challenge);

        return redirect()->route('two-factor.challenge')->with(
            $challenge['mail_sent'] ? 'status' : 'error',
            $challenge['mail_sent']
                ? 'Your account was created. We sent a one-time verification code to '.$user->email.'.'
                : 'Your account was created, but we could not send the verification code. Check the mail settings, then request a new code.'
        );
    }
}
