<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\OAuthCallbackUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\Shared\Domain\Service\RandomStringGenerator;

final class OAuthCallbackUseCase
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RandomStringGenerator $random
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
        $randomPassword = $this->random->generate(32);

        return $this->users->upsertOAuthUser(
            email: $email,
            name: $displayName,
            provider: $request->provider,
            emailVerifiedAt: new \DateTimeImmutable(),
            plainPassword: $randomPassword,
        );
    }
}


