<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\GetUserPreferencesUseCase;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\UpdateUserPreferencesUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\Service\UserPreferencesUpdater;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Locale;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\NeutralColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\PrimaryColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Theme;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Timezone;
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
            userPreferencesRepository: $this->repository,
            getUserPreferencesUseCase: $getUseCase,
            userPreferencesUpdater: new UserPreferencesUpdater()
        );
    }

    public function test_it_updates_locale_and_theme(): void
    {
        $userId = new UserId(5);
        $existing = UserPreferences::default($userId);
        $updated = UserPreferences::create(
            $userId,
            new Locale('en'),
            new Theme('dark'),
            new PrimaryColor('emerald'),
            new NeutralColor('gray'),
            new Timezone('Europe/Madrid')
        );

        $this->repository
            ->expects($this->exactly(2))
            ->method('find')
            ->with($userId)
            ->willReturnOnConsecutiveCalls($existing, $updated);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (UserPreferences $preferences) {
                return $preferences->locale()->toString() === 'en'
                    && $preferences->theme()->toString() === 'dark'
                    && $preferences->primaryColor()->toString() === 'emerald'
                    && $preferences->neutralColor()->toString() === 'gray'
                    && $preferences->timezone()->toString() === 'Europe/Madrid';
            }));

        $response = $this->useCase->execute(
            new UpdateUserPreferencesUseCaseRequest(
                userId: $userId->toInt(),
                locale: 'en',
                theme: 'dark',
                primaryColor: 'emerald',
                neutralColor: 'gray',
                timezone: 'Europe/Madrid',
            )
        );

        $this->assertSame('en', $response->data['locale']);
        $this->assertSame('dark', $response->data['theme']);
        $this->assertSame('emerald', $response->data['primary_color']);
        $this->assertSame('gray', $response->data['neutral_color']);
        $this->assertSame('Europe/Madrid', $response->data['timezone']);
    }
}
