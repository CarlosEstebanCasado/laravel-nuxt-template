<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\CreateUserUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\CreateUserUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserName;
use App\Src\Shared\Domain\ValueObject\DateTimeValue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Shared\Mother\EmailMother;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\WordMother;

final class CreateUserUseCaseTest extends TestCase
{
    private MockObject $users;

    private CreateUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = $this->createMock(UserRepository::class);
        $this->useCase = new CreateUserUseCase(userRepository: $this->users);
    }

    public function test_it_creates_password_user(): void
    {
        $request = new CreateUserUseCaseRequest(
            name: WordMother::random(),
            email: EmailMother::random(),
            password: WordMother::random()
        );
        $userId = new UserId(IntegerMother::random());

        $this->users
            ->expects($this->once())
            ->method('createPasswordUser')
            ->with(
                new UserName($request->name),
                self::equalTo(new EmailAddress($request->email)),
                $request->password,
                $this->isInstanceOf(DateTimeValue::class)
            )
            ->willReturn($userId);

        $result = $this->useCase->execute(request: $request);

        $this->assertSame($userId, $result);
    }
}
