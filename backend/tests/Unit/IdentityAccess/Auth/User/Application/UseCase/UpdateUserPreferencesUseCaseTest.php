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
use App\Src\Shared\Domain\Service\ConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase as BaseTestCase;

final class UpdateUserPreferencesUseCaseTest extends BaseTestCase
{
    private MockObject $repository;
    private MockObject $configProvider;
    private UpdateUserPreferencesUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(UserPreferencesRepository::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->configProvider->method('get')->willReturnCallback(
            function (string $key, mixed $default = null): mixed {
                $values = [
                    'app.locale' => 'es',
                    'app.supported_locales' => ['es' => 'Español', 'en' => 'English'],
                    'preferences.default_theme' => 'system',
                    'preferences.themes' => ['system' => 'System', 'dark' => 'Dark'],
                    'preferences.default_primary_color' => 'blue',
                    'preferences.primary_colors' => ['emerald' => 'Emerald', 'blue' => 'Blue'],
                    'preferences.default_neutral_color' => 'slate',
                    'preferences.neutral_colors' => ['gray' => 'Gray', 'slate' => 'Slate'],
                    'preferences.default_timezone' => 'UTC',
                ];

                return array_key_exists($key, $values) ? $values[$key] : $default;
            }
        );

        $getUseCase = new GetUserPreferencesUseCase($this->repository, $this->configProvider);
        $this->useCase = new UpdateUserPreferencesUseCase(
            userPreferencesRepository: $this->repository,
            getUserPreferencesUseCase: $getUseCase,
            userPreferencesUpdater: new UserPreferencesUpdater($this->configProvider),
            configProvider: $this->configProvider
        );
    }

    public function test_it_updates_locale_and_theme(): void
    {
        $userId = new UserId(5);
        $existing = UserPreferences::default($userId, $this->configProvider);
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
