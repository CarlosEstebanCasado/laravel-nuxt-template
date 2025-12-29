<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('auth_provider')
                ->default('password')
                ->after('remember_token');

            $table->timestamp('password_set_at')
                ->nullable()
                ->after('auth_provider');
        });

        /*
         * Backfill strategy (legacy installs):
         *
         * - Password users: keep auth_provider="password" and set password_set_at=created_at.
         * - Legacy OAuth users (created before these columns existed): they typically had
         *   email_verified_at set at creation time in our OAuth callback. Those users do NOT
         *   know the random password we generate, so they must NOT be treated as password-based.
         *
         * Since older versions didn't persist provider identifiers, we use a best-effort detector:
         * email_verified_at == created_at (same write) => likely OAuth signup.
         */

        // Mark likely legacy OAuth users and ensure they are not forced through password step-up auth.
        DB::table('users')
            ->whereNotNull('email_verified_at')
            ->whereColumn('email_verified_at', 'created_at')
            ->update([
                'auth_provider' => 'oauth',
                'password_set_at' => null,
            ]);

        // Backfill password_set_at only for password-based accounts.
        DB::table('users')
            ->where('auth_provider', 'password')
            ->whereNull('password_set_at')
            ->update(['password_set_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['auth_provider', 'password_set_at']);
        });
    }
};

