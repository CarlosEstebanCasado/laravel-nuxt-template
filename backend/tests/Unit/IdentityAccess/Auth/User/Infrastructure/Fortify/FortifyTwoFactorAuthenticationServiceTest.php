<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Infrastructure\Fortify;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Fortify\FortifyTwoFactorAuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Fortify;
use Tests\TestCase;

final class FortifyTwoFactorAuthenticationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_disables_two_factor_authentication(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $service = $this->app->make(FortifyTwoFactorAuthenticationService::class);

        $service->disableForUser(new UserId($user->id));

        $freshUser = $user->fresh();
        $this->assertNotNull($freshUser);
        $this->assertNull($freshUser->two_factor_secret);
        $this->assertNull($freshUser->two_factor_recovery_codes);
        $this->assertNull($freshUser->two_factor_confirmed_at);
    }
}
