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

        $resetUser = User::query()->updateOrCreate(
            ['email' => 'resetuser@example.com'],
            [
                'name' => 'Reset User',
                'password' => Hash::make('password'),
            ]
        );
        $resetUser->forceFill([
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $unverifiedUser = User::query()->updateOrCreate(
            ['email' => 'unverified@example.com'],
            [
                'name' => 'Unverified User',
                'password' => Hash::make('password'),
            ]
        );
        $unverifiedUser->forceFill([
            'email_verified_at' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $passwordUser = User::query()->updateOrCreate(
            ['email' => 'passworduser@example.com'],
            [
                'name' => 'Password User',
                'password' => Hash::make('password'),
            ]
        );
        $passwordUser->forceFill([
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $profileUser = User::query()->updateOrCreate(
            ['email' => 'profileuser@example.com'],
            [
                'name' => 'Profile User',
                'password' => Hash::make('password'),
            ]
        );
        $profileUser->forceFill([
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $preferencesUser = User::query()->updateOrCreate(
            ['email' => 'preferencesuser@example.com'],
            [
                'name' => 'Preferences User',
                'password' => Hash::make('password'),
            ]
        );
        $preferencesUser->forceFill([
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }
}
