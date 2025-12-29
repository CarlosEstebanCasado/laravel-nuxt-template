<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Session\Application\UseCase;

use App\Src\IdentityAccess\Session\Application\Request\RevokeSessionUseCaseRequest;
use App\Src\IdentityAccess\Session\Application\UseCase\RevokeSessionUseCase;
use App\Src\IdentityAccess\Session\Domain\Exception\CannotRevokeCurrentSession;
use App\Src\IdentityAccess\Session\Domain\Repository\SessionRepository;
use App\Src\Shared\Domain\Service\AuditEventRecorder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\IpMother;
use Tests\Unit\Shared\Mother\UrlMother;
use Tests\Unit\Shared\Mother\UserAgentMother;
use Tests\Unit\Shared\Mother\WordMother;

final class RevokeSessionUseCaseTest extends TestCase
{
    private MockObject $sessions;
    private MockObject $audit;
    private RevokeSessionUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessions = $this->createMock(SessionRepository::class);
        $this->audit = $this->createMock(AuditEventRecorder::class);
        $this->useCase = new RevokeSessionUseCase(
            sessionRepository: $this->sessions,
            auditEventRecorder: $this->audit
        );
    }

    public function test_it_throws_when_revoking_current_session(): void
    {
        $sessionId = WordMother::random();
        $request = new RevokeSessionUseCaseRequest(
            userId: IntegerMother::random(),
            sessionId: $sessionId,
            currentSessionId: $sessionId,
            url: UrlMother::random(),
            ipAddress: IpMother::random(),
            userAgent: UserAgentMother::random(),
            auditNewValues: []
        );

        $this->expectException(CannotRevokeCurrentSession::class);

        $this->useCase->execute(request: $request);
    }

    public function test_it_returns_false_when_session_not_found(): void
    {
        $request = new RevokeSessionUseCaseRequest(
            userId: IntegerMother::random(),
            sessionId: WordMother::random(),
            currentSessionId: WordMother::random(),
            url: UrlMother::random(),
            ipAddress: IpMother::random(),
            userAgent: UserAgentMother::random(),
            auditNewValues: []
        );

        $this->sessions
            ->expects($this->once())
            ->method('deleteForUser')
            ->with($request->sessionId, $request->userId)
            ->willReturn(0);

        $this->audit->expects($this->never())->method('recordUserEvent');

        $result = $this->useCase->execute(request: $request);

        $this->assertFalse($result);
    }

    public function test_it_revokes_session_and_records_audit(): void
    {
        $request = new RevokeSessionUseCaseRequest(
            userId: IntegerMother::random(),
            sessionId: WordMother::random(),
            currentSessionId: WordMother::random(),
            url: UrlMother::random(),
            ipAddress: IpMother::random(),
            userAgent: UserAgentMother::random(),
            auditNewValues: []
        );

        $this->sessions
            ->expects($this->once())
            ->method('deleteForUser')
            ->with($request->sessionId, $request->userId)
            ->willReturn(1);

        $this->audit
            ->expects($this->once())
            ->method('recordUserEvent')
            ->with(
                $request->userId,
                'session_revoked',
                [],
                ['session_id' => $request->sessionId],
                $request->url,
                $request->ipAddress,
                $request->userAgent,
                'security'
            );

        $result = $this->useCase->execute(request: $request);

        $this->assertTrue($result);
    }

    public function test_it_uses_custom_audit_new_values_when_provided(): void
    {
        $customNewValues = ['custom' => 'value'];
        $request = new RevokeSessionUseCaseRequest(
            userId: IntegerMother::random(),
            sessionId: WordMother::random(),
            currentSessionId: WordMother::random(),
            url: UrlMother::random(),
            ipAddress: IpMother::random(),
            userAgent: UserAgentMother::random(),
            auditNewValues: $customNewValues
        );

        $this->sessions
            ->expects($this->once())
            ->method('deleteForUser')
            ->with($request->sessionId, $request->userId)
            ->willReturn(1);

        $this->audit
            ->expects($this->once())
            ->method('recordUserEvent')
            ->with(
                $request->userId,
                'session_revoked',
                [],
                $customNewValues,
                $request->url,
                $request->ipAddress,
                $request->userAgent,
                'security'
            );

        $result = $this->useCase->execute(request: $request);

        $this->assertTrue($result);
    }
}
