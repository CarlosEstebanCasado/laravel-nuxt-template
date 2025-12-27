<?php
declare(strict_types=1);

namespace Tests\Unit\IdentityAccess\Audit\Application\UseCase;

use App\Src\IdentityAccess\Audit\Application\Converter\AuditListConverter;
use App\Src\IdentityAccess\Audit\Application\Converter\AuditResponseItemConverter;
use App\Src\IdentityAccess\Audit\Application\Request\ListUserAuditsUseCaseRequest;
use App\Src\IdentityAccess\Audit\Application\UseCase\ListUserAuditsUseCase;
use App\Src\IdentityAccess\Audit\Domain\Repository\AuditRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Unit\IdentityAccess\Audit\Domain\Response\AuditCollectionResponseMother;
use Tests\Unit\Shared\Mother\IntegerMother;
use Tests\Unit\Shared\Mother\WordMother;

final class ListUserAuditsUseCaseTest extends TestCase
{
    private MockObject $auditRepository;
    private AuditListConverter $responseConverter;
    private ListUserAuditsUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditRepository = $this->createMock(AuditRepository::class);
        $this->responseConverter = new AuditListConverter(new AuditResponseItemConverter());
        $this->useCase = new ListUserAuditsUseCase(
            auditRepository: $this->auditRepository,
            responseConverter: $this->responseConverter
        );
    }

    public function test_it_returns_converted_audit_list(): void
    {
        $request = new ListUserAuditsUseCaseRequest(
            auditableType: WordMother::random(),
            auditableId: IntegerMother::random(),
            perPage: IntegerMother::random(),
            page: IntegerMother::random()
        );
        $collectionResponse = AuditCollectionResponseMother::random();
        $expectedResponse = (new AuditListConverter(new AuditResponseItemConverter()))->toResponse($collectionResponse);

        $this->auditRepository
            ->expects($this->once())
            ->method('paginateForAuditable')
            ->with(
                $request->auditableType,
                $request->auditableId,
                $request->perPage,
                $request->page
            )
            ->willReturn($collectionResponse);

        $response = $this->useCase->execute(request: $request);

        $this->assertEquals($expectedResponse, $response);
    }
}
