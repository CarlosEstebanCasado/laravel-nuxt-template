<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthProviderMigrationTest extends TestCase
{
    /**
     * This test simulates a pre-migration database (no auth_provider/password_set_at columns),
     * inserts legacy users, then runs the migration and asserts the backfill is correct.
     */
    public function test_migration_does_not_lock_out_legacy_oauth_users(): void
    {
        // Ensure a clean slate (this test does NOT use RefreshDatabase on purpose).
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');

        // Pre-migration schema (matches 0001_01_01_000000_create_users_table.php).
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        $now = now()->startOfSecond();

        // Legacy OAuth-like user: email_verified_at set at creation time (same timestamp).
        DB::table('users')->insert([
            'name' => 'Legacy OAuth',
            'email' => 'legacy-oauth@example.com',
            'email_verified_at' => $now,
            'password' => 'hashed-random-password',
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Legacy password-like user: email_verified_at not set at creation time.
        DB::table('users')->insert([
            'name' => 'Legacy Password',
            'email' => 'legacy-password@example.com',
            'email_verified_at' => null,
            'password' => 'hashed-user-password',
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Run the migration under test.
        $migration = require base_path('database/migrations/2025_12_21_225200_add_auth_provider_to_users_table.php');
        $migration->up();

        $oauth = DB::table('users')->where('email', 'legacy-oauth@example.com')->first();
        $password = DB::table('users')->where('email', 'legacy-password@example.com')->first();

        $this->assertNotNull($oauth);
        $this->assertNotNull($password);

        $this->assertSame('oauth', $oauth->auth_provider);
        $this->assertNull($oauth->password_set_at);

        $this->assertSame('password', $password->auth_provider);
        $this->assertNotNull($password->password_set_at);
    }
}


