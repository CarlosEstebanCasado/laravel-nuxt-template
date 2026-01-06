<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserProfileUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\UpdateUserProfileResult;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\UpdateUserProfileUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Shared\Mother\EmailMother;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\WordMother;

final class UpdateUserProfileUseCaseTest extends TestCase
{
    private MockObject $users;

    private UpdateUserProfileUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = $this->createMock(UserRepository::class);
        $this->useCase = new UpdateUserProfileUseCase(userRepository: $this->users);
    }

    public function test_it_resets_email_verification_when_required(): void
    {
        $request = new UpdateUserProfileUseCaseRequest(
            userId: IntegerMother::random(),
            name: WordMother::random(),
            email: EmailMother::random(),
            isEmailChanging: true,
            mustVerifyEmail: true
        );

        $this->users
            ->expects($this->once())
            ->method('updateProfile')
            ->with(
                self::equalTo(new UserId($request->userId)),
                new UserName($request->name),
                self::equalTo(new EmailAddress($request->email)),
                true
            );

        $result = $this->useCase->execute(request: $request);

        $this->assertInstanceOf(UpdateUserProfileResult::class, $result);
        $this->assertTrue($result->shouldSendEmailVerificationNotification);
    }

    public function test_it_keeps_verification_when_not_required(): void
    {
        $request = new UpdateUserProfileUseCaseRequest(
            userId: IntegerMother::random(),
            name: WordMother::random(),
            email: EmailMother::random(),
            isEmailChanging: false,
            mustVerifyEmail: true
        );

        $this->users
            ->expects($this->once())
            ->method('updateProfile')
            ->with(
                self::equalTo(new UserId($request->userId)),
                new UserName($request->name),
                self::equalTo(new EmailAddress($request->email)),
                false
            );

        $result = $this->useCase->execute(request: $request);

        $this->assertFalse($result->shouldSendEmailVerificationNotification);
    }
}
