<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Response;

final class UserPreferencesResponseItem
{
    public function __construct(
        public string $locale,
        public string $theme,
        public string $primary_color,
        public string $neutral_color,
    ) {
    }
}
