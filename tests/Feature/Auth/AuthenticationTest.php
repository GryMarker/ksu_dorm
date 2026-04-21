<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\LoginTwoFactorCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $code = null;

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('two-factor.challenge'));

        $session = $this->app['session.store'];
        $challenge = $session->get('auth.two_factor_login');

        Notification::assertSentTo($user, LoginTwoFactorCode::class, function (LoginTwoFactorCode $notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $verifyResponse = $this->post('/login/verify', [
            'code' => $code,
        ]);

        $this->assertAuthenticatedAs($user);
        $verifyResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertNull(Cache::get('login_2fa:'.$challenge['attempt_id']));
        $this->assertNotNull($user->fresh()->login_two_factor_confirmed_at);
    }

    public function test_two_factor_can_be_bypassed_when_testing_bypass_is_enabled(): void
    {
        Notification::fake();
        config(['auth.bypass_two_factor' => true]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
        $response->assertCookie('trusted_device');
        Notification::assertNothingSent();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        Notification::assertNothingSent();
    }

    public function test_users_can_not_complete_login_with_an_invalid_two_factor_code(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->post('/login/verify', [
            'code' => '000000',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('code');
    }

    public function test_dorm_masters_must_complete_two_factor_authentication(): void
    {
        $this->assertRoleMustCompleteTwoFactorAuthentication(User::ROLE_DORM_MASTER);
    }

    public function test_presidents_must_complete_two_factor_authentication(): void
    {
        $this->assertRoleMustCompleteTwoFactorAuthentication(User::ROLE_PRESIDENT);
    }

    private function assertRoleMustCompleteTwoFactorAuthentication(string $role): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => $role,
        ]);
        $code = null;

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('two-factor.challenge'));

        $session = $this->app['session.store'];
        $challenge = $session->get('auth.two_factor_login');

        $this->assertIsArray($challenge);

        Notification::assertSentTo($user, LoginTwoFactorCode::class, function (LoginTwoFactorCode $notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $verifyResponse = $this->post('/login/verify', [
            'code' => $code,
        ]);

        $this->assertAuthenticatedAs($user);
        $verifyResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertNull(Cache::get('login_2fa:'.$challenge['attempt_id']));
    }

    public function test_two_factor_verification_sets_a_trusted_device_cookie(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $code = null;
        $userAgent = 'TrustedDeviceTest/1.0';

        $this->withHeader('User-Agent', $userAgent)->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        Notification::assertSentTo($user, LoginTwoFactorCode::class, function (LoginTwoFactorCode $notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $verifyResponse = $this->withHeader('User-Agent', $userAgent)->post('/login/verify', [
            'code' => $code,
        ]);

        $verifyResponse->assertRedirect(route('dashboard', absolute: false));
        $verifyResponse->assertCookie('trusted_device');
    }

    public function test_trusted_device_cookie_skips_future_two_factor_challenges(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => User::ROLE_DORM_MASTER,
        ]);
        $code = null;
        $userAgent = 'TrustedDeviceRepeatLoginTest/1.0';

        $this->withHeader('User-Agent', $userAgent)->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        Notification::assertSentTo($user, LoginTwoFactorCode::class, function (LoginTwoFactorCode $notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $verifyResponse = $this->withHeader('User-Agent', $userAgent)->post('/login/verify', [
            'code' => $code,
        ]);

        $verifyResponse->assertCookie('trusted_device');

        $this->post('/logout');
        Notification::fake();

        $this->withHeader('User-Agent', $userAgent)
            ->withCookie('trusted_device', json_encode([
                'user_id' => $user->id,
                'user_agent_hash' => hash('sha256', $userAgent),
                'expires_at' => now()->addDays(90)->toIso8601String(),
            ]))
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        Notification::assertNothingSent();
    }

    public function test_completed_two_factor_login_skips_future_challenges_without_cookie(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => User::ROLE_DORM_MASTER,
        ]);
        $code = null;

        $this->withHeader('User-Agent', 'FirstDevice/1.0')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        Notification::assertSentTo($user, LoginTwoFactorCode::class, function (LoginTwoFactorCode $notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $this->withHeader('User-Agent', 'FirstDevice/1.0')->post('/login/verify', [
            'code' => $code,
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->post('/logout');
        Notification::fake();

        $this->withHeader('User-Agent', 'DifferentDevice/1.0')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        Notification::assertNothingSent();
    }

    public function test_email_verification_notice_sends_a_code(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $code = null;

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();

        Notification::assertSentTo($user, LoginTwoFactorCode::class, function (LoginTwoFactorCode $notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $this->post('/login/verify', [
            'code' => $code,
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_signed_email_verification_url_is_not_registered(): void
    {
        $this->get('/verify-email/1/not-a-code')->assertNotFound();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
