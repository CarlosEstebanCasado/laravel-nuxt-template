<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\GetUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\GetUserPreferencesUseCase;
use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Locale;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\NeutralColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\PrimaryColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Theme;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Timezone;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\Shared\Domain\Service\ConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase as BaseTestCase;

final class GetUserPreferencesUseCaseTest extends BaseTestCase
{
    private MockObject $repository;
    private ConfigProvider&MockObject $configProvider;
    private GetUserPreferencesUseCase $useCase;

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

        $this->useCase = new GetUserPreferencesUseCase($this->repository, $this->configProvider);
    }

    public function test_it_returns_default_preferences_when_missing(): void
    {
        $userId = new UserId(1);

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($userId)
            ->willReturn(null);

        $response = $this->useCase->execute(
            new GetUserPreferencesUseCaseRequest(userId: $userId->toInt())
        );

        $this->assertSame('es', $response->data['locale']);
        $this->assertSame('system', $response->data['theme']);
        $this->assertSame('blue', $response->data['primary_color']);
        $this->assertSame('slate', $response->data['neutral_color']);
        $this->assertSame('UTC', $response->data['timezone']);
        $this->assertNotEmpty($response->available_locales);
        $this->assertNotEmpty($response->available_themes);
    }

    public function test_it_returns_saved_preferences(): void
    {
        $userId = new UserId(10);
        $preferences = UserPreferences::create(
            $userId,
            new Locale('ca'),
            new Theme('dark'),
            new PrimaryColor('emerald'),
            new NeutralColor('slate'),
            new Timezone('Europe/Madrid')
        );

        $this->repository
            ->expects($this->once())
            ->method('find')
            ->with($userId)
            ->willReturn($preferences);

        $response = $this->useCase->execute(
            new GetUserPreferencesUseCaseRequest(userId: $userId->toInt())
        );

        $this->assertSame('ca', $response->data['locale']);
        $this->assertSame('dark', $response->data['theme']);
        $this->assertSame('emerald', $response->data['primary_color']);
        $this->assertSame('slate', $response->data['neutral_color']);
        $this->assertSame('Europe/Madrid', $response->data['timezone']);
    }
}
