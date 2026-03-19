<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\LoginTwoFactorCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ]);

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $code = null;

        $this->assertGuest();
        $response->assertRedirect(route('two-factor.challenge'));

        Notification::assertSentTo($user, LoginTwoFactorCode::class, function (LoginTwoFactorCode $notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $session = $this->app['session.store'];
        $challenge = $session->get('auth.two_factor_login');

        $verifyResponse = $this->post('/login/verify', [
            'code' => $code,
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $verifyResponse->assertRedirect(route('tenant.apply.form', absolute: false));
        $this->assertNull(Cache::get('login_2fa:'.$challenge['attempt_id']));
    }
}
