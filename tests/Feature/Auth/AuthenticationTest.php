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

    public function test_dorm_masters_can_log_in_without_two_factor_authentication(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => User::ROLE_DORM_MASTER,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
        Notification::assertNothingSent();
        $this->assertNull($this->app['session.store']->get('auth.two_factor_login'));
    }

    public function test_tenant_two_factor_verification_sets_a_trusted_device_cookie(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => User::ROLE_TENANT,
        ]);
        $code = null;
        $userAgent = 'TenantTrustedDeviceTest/1.0';

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
        $verifyResponse->assertCookie('tenant_trusted_device');
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
