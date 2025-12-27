<?php

namespace Tests\Feature;

use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Contracts\Session\Session as SessionContract;
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

    /** @var array<string, string> */
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
        /** @var SessionContract $session */
        $session = $this->app['session'];
        $session->setId($sessionId);
        $this->withCredentials();
        $cookieName = config('session.cookie');
        $cookieName = is_string($cookieName) && $cookieName !== '' ? $cookieName : 'laravel_session';
        $this->withUnencryptedCookie($cookieName, $sessionId);
        $this->withSession(['_token' => $this->csrfToken, 'init' => true]);

        return $sessionId;
    }

    public function test_user_can_revoke_other_sessions_and_it_creates_an_audit_event(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        /** @var User $otherUser */
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
        $revokedValue = data_get($audit->getAttribute('new_values'), 'revoked', 0);
        $revoked = is_int($revokedValue)
            ? $revokedValue
            : (is_numeric($revokedValue) ? (int) $revokedValue : 0);
        $this->assertSame(2, $revoked);
    }

    public function test_user_can_revoke_a_specific_session_and_it_creates_an_audit_event(): void
    {
        /** @var User $user */
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
        $sessionIdValue = data_get($audit->getAttribute('new_values'), 'session_id', '');
        $sessionId = is_string($sessionIdValue) ? $sessionIdValue : '';
        $this->assertSame('revokable_session', $sessionId);
    }

    public function test_audits_endpoint_returns_user_audits(): void
    {
        /** @var User $user */
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

        /** @var array<int, array<string, mixed>> $data */
        $data = $response->json('data');
        $events = collect($data)->pluck('event')->all();
        $this->assertContains('sessions_revoked', $events);
    }
}
