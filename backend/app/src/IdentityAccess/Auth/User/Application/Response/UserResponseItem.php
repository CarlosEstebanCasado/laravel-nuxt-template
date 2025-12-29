<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Response;

final class UserResponseItem
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $email_verified_at,
        public string $auth_provider,
        public ?string $password_set_at,
        public ?string $created_at,
        public ?string $updated_at,
        public ?UserPreferencesResponseItem $preferences = null,
    ) {
    }
}
