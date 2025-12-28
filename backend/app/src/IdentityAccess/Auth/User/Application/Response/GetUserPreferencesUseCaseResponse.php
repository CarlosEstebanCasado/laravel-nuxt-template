<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Response;

/**
 * @phpstan-type PreferenceOption array{value:string,label:string}
 */
final class GetUserPreferencesUseCaseResponse
{
    /**
     * @param array{locale:string,theme:string} $data
     * @param array<int, PreferenceOption> $available_locales
     * @param array<int, PreferenceOption> $available_themes
     */
    public function __construct(
        public array $data,
        public array $available_locales,
        public array $available_themes,
    ) {
    }
}
