<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Security\Reauth\Application\UseCase;

use App\Src\IdentityAccess\Security\Reauth\Application\Request\DeleteAccountUseCaseRequest;
use App\Src\IdentityAccess\Security\Reauth\Application\Response\DeleteAccountUseCaseResponse;
use App\Src\IdentityAccess\Security\Reauth\Application\UseCase\DeleteAccountUseCase;
use App\Src\IdentityAccess\Security\Reauth\Domain\Repository\AccountRepository;
use App\Src\Shared\Domain\Service\AuditEventRecorder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\IpMother;
use Tests\Unit\Shared\Mother\UrlMother;
use Tests\Unit\Shared\Mother\UserAgentMother;
use Tests\Unit\Shared\Mother\WordMother;

final class DeleteAccountUseCaseTest extends TestCase
{
    private MockObject $accounts;
    private MockObject $audit;
    private DeleteAccountUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accounts = $this->createMock(AccountRepository::class);
        $this->audit = $this->createMock(AuditEventRecorder::class);
        $this->useCase = new DeleteAccountUseCase(
            accountRepository: $this->accounts,
            auditEventRecorder: $this->audit
        );
    }

    public function test_it_records_audit_and_deletes_account(): void
    {
        $request = new DeleteAccountUseCaseRequest(
            userId: IntegerMother::random(),
            confirmation: WordMother::random(),
            url: UrlMother::random(),
            ipAddress: IpMother::random(),
            userAgent: UserAgentMother::random()
        );

        $this->audit
            ->expects($this->once())
            ->method('recordUserEvent')
            ->with(
                $request->userId,
                'account_deleted',
                [],
                ['confirmation' => $request->confirmation],
                $request->url,
                $request->ipAddress,
                $request->userAgent,
                'security'
            );

        $this->accounts
            ->expects($this->once())
            ->method('deleteAccount')
            ->with($request->userId);

        $response = $this->useCase->execute(request: $request);

        $this->assertInstanceOf(DeleteAccountUseCaseResponse::class, $response);
    }
}
