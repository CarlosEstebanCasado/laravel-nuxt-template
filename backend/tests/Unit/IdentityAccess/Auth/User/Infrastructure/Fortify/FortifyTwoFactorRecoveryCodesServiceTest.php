<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Infrastructure\Fortify;

use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Fortify\FortifyTwoFactorRecoveryCodesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Fortify;
use Tests\TestCase;

final class FortifyTwoFactorRecoveryCodesServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_recovery_codes_for_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['code-1', 'code-2'])),
        ]);

        $service = $this->app->make(FortifyTwoFactorRecoveryCodesService::class);

        $collection = $service->getForUser(new UserId($user->id));

        $this->assertSame(['code-1', 'code-2'], $collection->values());
    }

    public function test_it_regenerates_recovery_codes(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['old-code'])),
        ]);

        $service = $this->app->make(FortifyTwoFactorRecoveryCodesService::class);

        $collection = $service->regenerateForUser(new UserId($user->id));

        $this->assertCount(8, $collection->values());
    }

    public function test_it_does_not_regenerate_when_two_factor_is_disabled(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);

        $service = $this->app->make(FortifyTwoFactorRecoveryCodesService::class);

        $collection = $service->regenerateForUser(new UserId($user->id));

        $this->assertSame([], $collection->values());
        $freshUser = $user->fresh();
        $this->assertNotNull($freshUser);
        $this->assertNull($freshUser->two_factor_recovery_codes);
    }
}
