<?php

namespace Database\Seeders;

use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $defaultUser = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );
        $defaultUser->forceFill([
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $twoFactorUser = User::query()->updateOrCreate(
            ['email' => 'twofactor@example.com'],
            [
                'name' => 'Two Factor User',
                'password' => Hash::make('password'),
            ]
        );
        $twoFactorUser->forceFill([
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $deleteUser = User::query()->updateOrCreate(
            ['email' => 'deleteuser@example.com'],
            [
                'name' => 'Delete User',
                'password' => Hash::make('password'),
            ]
        );
        $deleteUser->forceFill([
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }
}
