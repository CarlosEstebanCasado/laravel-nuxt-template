<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Response;

/**
 * @phpstan-type PreferenceOption array{value:string,label:string}
 */
final class GetUserPreferencesUseCaseResponse
{
    /**
     * @param array{locale:string,theme:string,primary_color:string,neutral_color:string} $data
     * @param array<int, PreferenceOption> $available_locales
     * @param array<int, PreferenceOption> $available_themes
     * @param array<int, PreferenceOption> $available_primary_colors
     * @param array<int, PreferenceOption> $available_neutral_colors
     */
    public function __construct(
        public array $data,
        public array $available_locales,
        public array $available_themes,
        public array $available_primary_colors,
        public array $available_neutral_colors,
    ) {
    }
}
