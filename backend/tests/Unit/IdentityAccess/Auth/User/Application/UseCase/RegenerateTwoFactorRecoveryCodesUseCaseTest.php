<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Converter\TwoFactorRecoveryCodeConverter;
use App\Src\IdentityAccess\Auth\User\Application\Converter\TwoFactorRecoveryCodesConverter;
use App\Src\IdentityAccess\Auth\User\Application\Request\RegenerateTwoFactorRecoveryCodesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\RegenerateTwoFactorRecoveryCodesUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Collection\RecoveryCodeCollection;
use App\Src\IdentityAccess\Auth\User\Domain\Service\TwoFactorRecoveryCodesService;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\RecoveryCode;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\WordMother;

final class RegenerateTwoFactorRecoveryCodesUseCaseTest extends TestCase
{
    private MockObject $twoFactorRecoveryCodesService;
    private TwoFactorRecoveryCodesConverter $twoFactorRecoveryCodesConverter;
    private RegenerateTwoFactorRecoveryCodesUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->twoFactorRecoveryCodesService = $this->createMock(TwoFactorRecoveryCodesService::class);
        $this->twoFactorRecoveryCodesConverter = new TwoFactorRecoveryCodesConverter(
            new TwoFactorRecoveryCodeConverter()
        );
        $this->useCase = new RegenerateTwoFactorRecoveryCodesUseCase(
            twoFactorRecoveryCodesService: $this->twoFactorRecoveryCodesService,
            twoFactorRecoveryCodesConverter: $this->twoFactorRecoveryCodesConverter
        );
    }

    public function test_it_returns_regenerated_codes_response(): void
    {
        $request = new RegenerateTwoFactorRecoveryCodesUseCaseRequest(userId: IntegerMother::random());
        $collection = new RecoveryCodeCollection([
            new RecoveryCode(WordMother::random()),
            new RecoveryCode(WordMother::random()),
        ]);
        $expected = (new TwoFactorRecoveryCodesConverter(new TwoFactorRecoveryCodeConverter()))
            ->toResponse($collection);

        $this->twoFactorRecoveryCodesService
            ->expects($this->once())
            ->method('regenerateForUser')
            ->with(self::equalTo(new UserId($request->userId)))
            ->willReturn($collection);

        $response = $this->useCase->execute(request: $request);

        $this->assertEquals($expected, $response);
    }
}
