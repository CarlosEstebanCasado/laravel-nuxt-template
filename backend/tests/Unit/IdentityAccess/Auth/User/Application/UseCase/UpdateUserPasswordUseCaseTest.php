<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserPasswordUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\UpdateUserPasswordUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\WordMother;

final class UpdateUserPasswordUseCaseTest extends TestCase
{
    private MockObject $users;
    private UpdateUserPasswordUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = $this->createMock(UserRepository::class);
        $this->useCase = new UpdateUserPasswordUseCase(userRepository: $this->users);
    }

    public function test_it_updates_user_password(): void
    {
        $request = new UpdateUserPasswordUseCaseRequest(
            userId: IntegerMother::random(),
            password: WordMother::random()
        );

        $this->users
            ->expects($this->once())
            ->method('updatePassword')
            ->with(
                self::equalTo(new UserId($request->userId)),
                $request->password,
                $this->isInstanceOf(\DateTimeImmutable::class)
            );

        $this->useCase->execute(request: $request);
    }
}
