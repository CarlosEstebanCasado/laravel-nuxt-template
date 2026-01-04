<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\DisableTwoFactorAuthenticationUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\DisableTwoFactorAuthenticationUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Service\TwoFactorAuthenticationService;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Shared\Mother\IntegerMother;

final class DisableTwoFactorAuthenticationUseCaseTest extends TestCase
{
    private MockObject $twoFactorAuthenticationService;
    private DisableTwoFactorAuthenticationUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->twoFactorAuthenticationService = $this->createMock(TwoFactorAuthenticationService::class);
        $this->useCase = new DisableTwoFactorAuthenticationUseCase(
            twoFactorAuthenticationService: $this->twoFactorAuthenticationService
        );
    }

    public function test_it_disables_two_factor_authentication(): void
    {
        $request = new DisableTwoFactorAuthenticationUseCaseRequest(userId: IntegerMother::random());

        $this->twoFactorAuthenticationService
            ->expects($this->once())
            ->method('disableForUser')
            ->with(self::equalTo(new UserId($request->userId)));

        $this->useCase->execute(request: $request);
    }
}
