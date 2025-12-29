<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\OAuthCallbackUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\AuthProvider;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserName;
use App\Src\Shared\Domain\Service\RandomStringGenerator;
use App\Src\Shared\Domain\ValueObject\DateTimeValue;

final class OAuthCallbackUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RandomStringGenerator $randomStringGenerator
    ) {
    }

    public function execute(OAuthCallbackUseCaseRequest $request): UserId
    {
        $email = new EmailAddress($request->email);

        $displayName = $request->name
            ?: $request->nickname
            ?: $email->toString();

        // Social accounts typically don't know our local password.
        // We still set a random one for completeness.
        $randomPassword = $this->randomStringGenerator->generate(32);

        return $this->userRepository->upsertOAuthUser(
            email: $email,
            name: new UserName($displayName),
            provider: new AuthProvider($request->provider),
            emailVerifiedAt: new DateTimeValue(new \DateTimeImmutable()),
            plainPassword: $randomPassword,
        );
    }
}
