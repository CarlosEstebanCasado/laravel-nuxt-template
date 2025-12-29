<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Converter;

use App\Src\IdentityAccess\Auth\User\Application\Response\UserResponseItem;
use App\Src\IdentityAccess\Auth\User\Domain\Entity\User;

final class UserResponseItemConverter
{
    public function toResponse(User $user): UserResponseItem
    {
        return new UserResponseItem(
            id: $user->id()->toInt(),
            name: $user->name()->toString(),
            email: $user->email()->toString(),
            email_verified_at: $this->formatDate($user->emailVerifiedAt()),
            auth_provider: $user->authProvider()->toString(),
            password_set_at: $this->formatDate($user->passwordSetAt()),
            created_at: $this->formatDate($user->createdAt()),
            updated_at: $this->formatDate($user->updatedAt()),
        );
    }

    private function formatDate(?\App\Src\Shared\Domain\ValueObject\DateTimeValue $date): ?string
    {
        return $date?->value()->format('Y-m-d\TH:i:sP');
    }
}
