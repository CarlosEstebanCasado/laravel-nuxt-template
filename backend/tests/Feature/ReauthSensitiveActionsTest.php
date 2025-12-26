<?php

namespace Tests\Feature;

use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReauthSensitiveActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_user_must_confirm_password_to_delete_account(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'auth_provider' => 'password',
            'password_set_at' => now(),
            'password' => 'secret-password-123',
        ]);

        // Missing password => 422
        $this->actingAs($user)
            ->deleteJson('/api/v1/account', [
                'confirmation' => 'DELETE',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Wrong password => 422
        $this->actingAs($user)
            ->deleteJson('/api/v1/account', [
                'confirmation' => 'DELETE',
                'password' => 'wrong-password',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Correct password => 200 and user deleted
        $this->actingAs($user)
            ->deleteJson('/api/v1/account', [
                'confirmation' => 'DELETE',
                'password' => 'secret-password-123',
            ])
            ->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_oauth_user_can_delete_account_without_password(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'auth_provider' => 'github',
            'password_set_at' => null,
            // OAuth users have a random password in our flow, but they don't know it.
            'password' => 'random-password-should-not-be-required',
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/v1/account', [
                'confirmation' => 'DELETE',
            ])
            ->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_password_user_must_confirm_password_to_change_email(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'auth_provider' => 'password',
            'password_set_at' => now(),
            'password' => 'secret-password-123',
            'email' => 'old@example.com',
        ]);

        // This action triggers an email verification notification. We don't want CI
        // to depend on any mail transport, so fake notifications.
        Notification::fake();

        // Fortify profile update is a web route that normally requires CSRF.
        // CSRF is not relevant to the re-auth rule we want to test here.
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->actingAs($user)
            ->putJson('/auth/user/profile-information', [
                'name' => $user->name,
                'email' => 'new@example.com',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        $this->actingAs($user)
            ->putJson('/auth/user/profile-information', [
                'name' => $user->name,
                'email' => 'new@example.com',
                'current_password' => 'wrong-password',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        $this->actingAs($user)
            ->putJson('/auth/user/profile-information', [
                'name' => $user->name,
                'email' => 'new@example.com',
                'current_password' => 'secret-password-123',
            ])
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
        ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}


