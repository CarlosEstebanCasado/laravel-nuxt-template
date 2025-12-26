<?php

namespace App\BoundedContext\Auth\User\Application\UseCase;

use App\BoundedContext\Auth\User\Application\Request\OAuthCallbackUseCaseRequest;
use App\BoundedContext\Auth\User\Domain\Repository\UserRepository;
use App\BoundedContext\Auth\User\Domain\ValueObject\EmailAddress;
use App\BoundedContext\Auth\User\Domain\ValueObject\UserId;
use App\BoundedContext\Shared\Shared\Domain\Service\PasswordHasher;
use App\BoundedContext\Shared\Shared\Domain\Service\RandomStringGenerator;

final class OAuthCallbackUseCase
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $hasher,
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
        $passwordHash = $this->hasher->hash($this->random->generate(32));

        return $this->users->upsertOAuthUser(
            email: $email,
            name: $displayName,
            provider: $request->provider,
            emailVerifiedAt: new \DateTimeImmutable(),
            passwordHash: $passwordHash,
        );
    }
}


