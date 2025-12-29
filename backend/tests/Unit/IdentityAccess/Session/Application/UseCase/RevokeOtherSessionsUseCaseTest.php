<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Session\Application\UseCase;

use App\Src\IdentityAccess\Session\Application\Request\RevokeOtherSessionsUseCaseRequest;
use App\Src\IdentityAccess\Session\Application\UseCase\RevokeOtherSessionsUseCase;
use App\Src\IdentityAccess\Session\Domain\Repository\SessionRepository;
use App\Src\Shared\Domain\Service\AuditEventRecorder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\IpMother;
use Tests\Unit\Shared\Mother\UrlMother;
use Tests\Unit\Shared\Mother\UserAgentMother;
use Tests\Unit\Shared\Mother\WordMother;

final class RevokeOtherSessionsUseCaseTest extends TestCase
{
    private MockObject $sessions;
    private MockObject $audit;
    private RevokeOtherSessionsUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessions = $this->createMock(SessionRepository::class);
        $this->audit = $this->createMock(AuditEventRecorder::class);
        $this->useCase = new RevokeOtherSessionsUseCase(
            sessionRepository: $this->sessions,
            auditEventRecorder: $this->audit
        );
    }

    public function test_it_revokes_sessions_and_records_audit(): void
    {
        $request = new RevokeOtherSessionsUseCaseRequest(
            userId: IntegerMother::random(),
            currentSessionId: WordMother::random(),
            url: UrlMother::random(),
            ipAddress: IpMother::random(),
            userAgent: UserAgentMother::random(),
            auditNewValues: []
        );
        $revoked = IntegerMother::random();

        $this->sessions
            ->expects($this->once())
            ->method('deleteOthersForUser')
            ->with($request->userId, $request->currentSessionId)
            ->willReturn($revoked);

        $this->audit
            ->expects($this->once())
            ->method('recordUserEvent')
            ->with(
                $request->userId,
                'sessions_revoked',
                [],
                ['revoked' => $revoked],
                $request->url,
                $request->ipAddress,
                $request->userAgent,
                'security'
            );

        $result = $this->useCase->execute(request: $request);

        $this->assertSame($revoked, $result);
    }

    public function test_it_uses_custom_audit_new_values_when_provided(): void
    {
        $customValues = ['foo' => 'bar'];
        $request = new RevokeOtherSessionsUseCaseRequest(
            userId: IntegerMother::random(),
            currentSessionId: WordMother::random(),
            url: UrlMother::random(),
            ipAddress: IpMother::random(),
            userAgent: UserAgentMother::random(),
            auditNewValues: $customValues
        );
        $revoked = IntegerMother::random();

        $this->sessions
            ->expects($this->once())
            ->method('deleteOthersForUser')
            ->with($request->userId, $request->currentSessionId)
            ->willReturn($revoked);

        $this->audit
            ->expects($this->once())
            ->method('recordUserEvent')
            ->with(
                $request->userId,
                'sessions_revoked',
                [],
                $customValues,
                $request->url,
                $request->ipAddress,
                $request->userAgent,
                'security'
            );

        $result = $this->useCase->execute(request: $request);

        $this->assertSame($revoked, $result);
    }
}
