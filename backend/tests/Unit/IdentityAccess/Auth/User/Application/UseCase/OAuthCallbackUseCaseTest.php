<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\OAuthCallbackUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\OAuthCallbackUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\AuthProvider;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserName;
use App\Src\Shared\Domain\Service\RandomStringGenerator;
use App\Src\Shared\Domain\ValueObject\DateTimeValue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Shared\Mother\EmailMother;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\WordMother;

final class OAuthCallbackUseCaseTest extends TestCase
{
    private MockObject $users;

    private MockObject $random;

    private OAuthCallbackUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = $this->createMock(UserRepository::class);
        $this->random = $this->createMock(RandomStringGenerator::class);
        $this->useCase = new OAuthCallbackUseCase(
            userRepository: $this->users,
            randomStringGenerator: $this->random
        );
    }

    public function test_it_uses_provided_name(): void
    {
        $name = WordMother::random();
        $request = new OAuthCallbackUseCaseRequest(
            provider: WordMother::random(),
            email: EmailMother::random(),
            name: $name,
            nickname: WordMother::random()
        );
        $password = WordMother::random();
        $userId = new UserId(IntegerMother::random());

        $this->random
            ->expects($this->once())
            ->method('generate')
            ->with(32)
            ->willReturn($password);

        $this->users
            ->expects($this->once())
            ->method('upsertOAuthUser')
            ->with(
                self::equalTo(new EmailAddress($request->email)),
                new UserName($name),
                new AuthProvider($request->provider),
                $this->isInstanceOf(DateTimeValue::class),
                $password
            )
            ->willReturn($userId);

        $result = $this->useCase->execute(request: $request);

        $this->assertSame($userId, $result);
    }

    public function test_it_uses_nickname_when_name_missing(): void
    {
        $nickname = WordMother::random();
        $request = new OAuthCallbackUseCaseRequest(
            provider: WordMother::random(),
            email: EmailMother::random(),
            name: null,
            nickname: $nickname
        );
        $password = WordMother::random();
        $userId = new UserId(IntegerMother::random());

        $this->random
            ->expects($this->once())
            ->method('generate')
            ->with(32)
            ->willReturn($password);

        $this->users
            ->expects($this->once())
            ->method('upsertOAuthUser')
            ->with(
                self::equalTo(new EmailAddress($request->email)),
                new UserName($nickname),
                new AuthProvider($request->provider),
                $this->isInstanceOf(DateTimeValue::class),
                $password
            )
            ->willReturn($userId);

        $result = $this->useCase->execute(request: $request);

        $this->assertSame($userId, $result);
    }

    public function test_it_uses_email_when_name_and_nickname_missing(): void
    {
        $email = EmailMother::random();
        $request = new OAuthCallbackUseCaseRequest(
            provider: WordMother::random(),
            email: $email,
            name: null,
            nickname: null
        );
        $password = WordMother::random();
        $userId = new UserId(IntegerMother::random());

        $this->random
            ->expects($this->once())
            ->method('generate')
            ->with(32)
            ->willReturn($password);

        $this->users
            ->expects($this->once())
            ->method('upsertOAuthUser')
            ->with(
                self::equalTo(new EmailAddress($request->email)),
                new UserName($email),
                new AuthProvider($request->provider),
                $this->isInstanceOf(DateTimeValue::class),
                $password
            )
            ->willReturn($userId);

        $result = $this->useCase->execute(request: $request);

        $this->assertSame($userId, $result);
    }
}
