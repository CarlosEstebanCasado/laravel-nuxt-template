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
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase as BaseTestCase;

final class GetUserPreferencesUseCaseTest extends BaseTestCase
{
    private MockObject $repository;
    private GetUserPreferencesUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(UserPreferencesRepository::class);
        $this->useCase = new GetUserPreferencesUseCase($this->repository);
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

        $this->assertSame(config('app.locale'), $response->data['locale']);
        $this->assertSame(config('preferences.default_theme'), $response->data['theme']);
        $this->assertSame(config('preferences.default_primary_color'), $response->data['primary_color']);
        $this->assertSame(config('preferences.default_neutral_color'), $response->data['neutral_color']);
        $this->assertSame(config('preferences.default_timezone'), $response->data['timezone']);
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
