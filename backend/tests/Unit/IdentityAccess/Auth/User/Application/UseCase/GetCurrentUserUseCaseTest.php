<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Converter\UserResponseConverter;
use App\Src\IdentityAccess\Auth\User\Application\Converter\UserResponseItemConverter;
use App\Src\IdentityAccess\Auth\User\Application\Request\GetCurrentUserUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\GetCurrentUserUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\IdentityAccess\Auth\User\Domain\Entity\UserMother;
use Tests\Unit\Shared\Mother\IntegerMother;

final class GetCurrentUserUseCaseTest extends TestCase
{
    private MockObject $users;
    private MockObject $preferences;
    private UserResponseConverter $converter;
    private GetCurrentUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = $this->createMock(UserRepository::class);
        $this->preferences = $this->createMock(UserPreferencesRepository::class);
        $this->converter = new UserResponseConverter(new UserResponseItemConverter());
        $this->useCase = new GetCurrentUserUseCase(
            userRepository: $this->users,
            userPreferencesRepository: $this->preferences,
            userResponseConverter: $this->converter
        );
    }

    public function test_it_returns_converted_user_response(): void
    {
        $request = new GetCurrentUserUseCaseRequest(userId: IntegerMother::random());
        $user = UserMother::withPasswordProvider();
        $expected = (new UserResponseConverter(new UserResponseItemConverter()))->toResponse($user, null);

        $this->users
            ->expects($this->once())
            ->method('get')
            ->with(self::equalTo(new UserId($request->userId)))
            ->willReturn($user);

        $this->preferences
            ->expects($this->once())
            ->method('find')
            ->with(self::equalTo(new UserId($request->userId)))
            ->willReturn(null);

        $response = $this->useCase->execute(request: $request);

        $this->assertEquals($expected, $response);
    }
}
