<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\ResetUserPasswordUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\ResetUserPasswordUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\WordMother;

final class ResetUserPasswordUseCaseTest extends TestCase
{
    private MockObject $users;
    private ResetUserPasswordUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = $this->createMock(UserRepository::class);
        $this->useCase = new ResetUserPasswordUseCase(userRepository: $this->users);
    }

    public function test_it_resets_password_without_updating_password_set_at(): void
    {
        $request = new ResetUserPasswordUseCaseRequest(
            userId: IntegerMother::random(),
            password: WordMother::random()
        );

        $this->users
            ->expects($this->once())
            ->method('updatePassword')
            ->with(
                self::equalTo(new UserId($request->userId)),
                $request->password,
                null
            );

        $this->useCase->execute(request: $request);
    }
}
