<?php

declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Session\Application\UseCase;

use App\Src\IdentityAccess\Session\Application\Converter\SessionListConverter;
use App\Src\IdentityAccess\Session\Application\Converter\SessionResponseItemConverter;
use App\Src\IdentityAccess\Session\Application\Request\ListUserSessionsUseCaseRequest;
use App\Src\IdentityAccess\Session\Application\UseCase\ListUserSessionsUseCase;
use App\Src\IdentityAccess\Session\Domain\Repository\SessionRepository;
use App\Src\IdentityAccess\Session\Domain\Service\SessionInfoCurrentMarker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\IdentityAccess\Session\Domain\Response\SessionCollectionResponseMother;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\WordMother;

final class ListUserSessionsUseCaseTest extends TestCase
{
    private MockObject $sessions;

    private SessionListConverter $converter;

    private ListUserSessionsUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessions = $this->createMock(SessionRepository::class);
        $this->converter = new SessionListConverter(
            new SessionResponseItemConverter,
            new SessionInfoCurrentMarker
        );
        $this->useCase = new ListUserSessionsUseCase(
            sessionRepository: $this->sessions,
            sessionListConverter: $this->converter
        );
    }

    public function test_it_returns_converted_session_collection(): void
    {
        $request = new ListUserSessionsUseCaseRequest(
            userId: IntegerMother::random(),
            currentSessionId: WordMother::random()
        );
        $collection = SessionCollectionResponseMother::random();
        $expectedConverter = new SessionListConverter(
            new SessionResponseItemConverter,
            new SessionInfoCurrentMarker
        );
        $expected = $expectedConverter->toResponse($collection, $request->currentSessionId);

        $this->sessions
            ->expects($this->once())
            ->method('listForUser')
            ->with($request->userId)
            ->willReturn($collection);

        $response = $this->useCase->execute(request: $request);

        $this->assertEquals($expected, $response);
    }
}
