<?php

namespace Tests\Feature;

use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Tests\TestCase;

class UpdatePasswordKeepsSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_update_keeps_current_session_authenticated(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'password' => 'old-password-123',
        ]);

        // Fortify password update is a web route that normally requires CSRF.
        // CSRF is not relevant to the behavior we want to test here.
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->actingAs($user)
            ->putJson('/auth/user/password', [
                'current_password' => 'old-password-123',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSuccessful();

        // The current session should remain valid (logoutOtherDevices should not blow up,
        // and AuthenticateSession should not force a logout on next request).
        $this->actingAs($user)
            ->getJson('/api/v1/me')
            ->assertOk();
    }
}


