<?php

namespace Tests\Feature;

use App\BoundedContext\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

class SessionsAuditTest extends TestCase
{
    use RefreshDatabase;

    private string $csrfToken = 'test_csrf_token';
    private string $validSessionId = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    private array $statefulHeaders = [
        'Origin' => 'https://app.project.dev',
        'Referer' => 'https://app.project.dev/',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // We set a fixed session id cookie for deterministic session testing.
        // Disable cookie encryption middleware so the raw cookie value is used as the session id.
        $this->withoutMiddleware(EncryptCookies::class);

        // Make failing responses show the underlying exception in test output.
        $this->withoutExceptionHandling();
    }

    private function bootStatefulSession(string $sessionId): string
    {
        $this->app['session']->setId($sessionId);
        $this->withCredentials();
        $this->withUnencryptedCookie(config('session.cookie'), $sessionId);
        $this->withSession(['_token' => $this->csrfToken, 'init' => true]);

        return $sessionId;
    }

    public function test_user_can_revoke_other_sessions_and_it_creates_an_audit_event(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);
        $currentSessionId = $this->bootStatefulSession($this->validSessionId);

        DB::table('sessions')->insert([
            [
                'id' => $currentSessionId,
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'payload' => 'test',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'other_session_1',
                'user_id' => $user->id,
                'ip_address' => '10.0.0.1',
                'user_agent' => 'Other',
                'payload' => 'test',
                'last_activity' => now()->subHour()->timestamp,
            ],
            [
                'id' => 'other_session_2',
                'user_id' => $user->id,
                'ip_address' => '10.0.0.2',
                'user_agent' => 'Other',
                'payload' => 'test',
                'last_activity' => now()->subHours(2)->timestamp,
            ],
            [
                'id' => 'other_user_session',
                'user_id' => $otherUser->id,
                'ip_address' => '10.0.0.3',
                'user_agent' => 'OtherUser',
                'payload' => 'test',
                'last_activity' => now()->subHours(3)->timestamp,
            ],
        ]);

        $response = $this->postJson(
            '/api/v1/sessions/revoke-others',
            [],
            [...$this->statefulHeaders, 'X-CSRF-TOKEN' => $this->csrfToken]
        );

        $response->assertOk()
            ->assertJsonPath('data.revoked', 2);

        $this->assertDatabaseCount('sessions', 2);
        $this->assertDatabaseHas('sessions', ['id' => $currentSessionId, 'user_id' => $user->id]);
        $this->assertDatabaseMissing('sessions', ['id' => 'other_session_1']);
        $this->assertDatabaseMissing('sessions', ['id' => 'other_session_2']);
        $this->assertDatabaseHas('sessions', ['id' => 'other_user_session', 'user_id' => $otherUser->id]);

        $audit = Audit::query()
            ->where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('event', 'sessions_revoked')
            ->latest()
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame(2, (int) ($audit->new_values['revoked'] ?? 0));
    }

    public function test_user_can_revoke_a_specific_session_and_it_creates_an_audit_event(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);
        $currentSessionId = $this->bootStatefulSession($this->validSessionId);

        DB::table('sessions')->insert([
            [
                'id' => $currentSessionId,
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'payload' => 'test',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'revokable_session',
                'user_id' => $user->id,
                'ip_address' => '10.0.0.1',
                'user_agent' => 'Other',
                'payload' => 'test',
                'last_activity' => now()->subHour()->timestamp,
            ],
        ]);

        $response = $this->deleteJson(
            '/api/v1/sessions/revokable_session',
            [],
            [...$this->statefulHeaders, 'X-CSRF-TOKEN' => $this->csrfToken]
        );

        $response->assertNoContent();

        $this->assertDatabaseHas('sessions', ['id' => $currentSessionId]);
        $this->assertDatabaseMissing('sessions', ['id' => 'revokable_session']);

        $audit = Audit::query()
            ->where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('event', 'session_revoked')
            ->latest()
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('revokable_session', (string) ($audit->new_values['session_id'] ?? ''));
    }

    public function test_audits_endpoint_returns_user_audits(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);
        $currentSessionId = $this->bootStatefulSession($this->validSessionId);

        // Trigger an audit by revoking a session.
        DB::table('sessions')->insert([
            [
                'id' => $currentSessionId,
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'payload' => 'test',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'other_session',
                'user_id' => $user->id,
                'ip_address' => '10.0.0.1',
                'user_agent' => 'Other',
                'payload' => 'test',
                'last_activity' => now()->subHour()->timestamp,
            ],
        ]);

        $this->postJson(
            '/api/v1/sessions/revoke-others',
            [],
            [...$this->statefulHeaders, 'X-CSRF-TOKEN' => $this->csrfToken]
        )->assertOk();

        $response = $this->getJson('/api/v1/audits', $this->statefulHeaders);

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $events = collect($response->json('data'))->pluck('event')->all();
        $this->assertContains('sessions_revoked', $events);
    }
}


