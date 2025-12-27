<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Security\Reauth\Application\Response;

final class DeleteAccountUseCaseResponse
{
    public function __construct(
        public string $message = 'Account deleted.'
    ) {
    }

    /**
     * @return array{message:string}
     */
    public function toArray(): array
    {
        return ['message' => $this->message];
    }
}

