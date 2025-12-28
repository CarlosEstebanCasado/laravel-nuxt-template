<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\GetUserPreferencesUseCase;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\UpdateUserPreferencesUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase as BaseTestCase;

final class UpdateUserPreferencesUseCaseTest extends BaseTestCase
{
    private MockObject $repository;
    private UpdateUserPreferencesUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(UserPreferencesRepository::class);
        $getUseCase = new GetUserPreferencesUseCase($this->repository);
        $this->useCase = new UpdateUserPreferencesUseCase(
            preferences: $this->repository,
            getUserPreferencesUseCase: $getUseCase
        );
    }

    public function test_it_updates_locale_and_theme(): void
    {
        $userId = new UserId(5);
        $existing = UserPreferences::default($userId);
        $updated = UserPreferences::create($userId, 'en', 'dark');

        $this->repository
            ->expects($this->exactly(2))
            ->method('find')
            ->with($userId)
            ->willReturnOnConsecutiveCalls($existing, $updated);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (UserPreferences $preferences) {
                return $preferences->locale() === 'en'
                    && $preferences->theme() === 'dark';
            }));

        $response = $this->useCase->execute(
            new UpdateUserPreferencesUseCaseRequest(
                userId: $userId->toInt(),
                locale: 'en',
                theme: 'dark'
            )
        );

        $this->assertSame('en', $response->data['locale']);
        $this->assertSame('dark', $response->data['theme']);
    }
}
