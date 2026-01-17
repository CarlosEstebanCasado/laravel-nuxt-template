<?php

declare(strict_types=1);

namespace App\Src\Shared\Infrastructure\Logging;

final class RedactSensitiveDataProcessor
{
    /**
     * @var array<string, bool>
     */
    private array $keys;

    public function __construct()
    {
        $raw = env(
            'LOG_REDACT_KEYS',
            'password,password_confirmation,current_password,token,access_token,refresh_token,api_key,api-key,secret,client_secret,authorization,cookie,set-cookie,xsrf-token,csrf-token,session,ssn,credit_card,card_number,cvc,email'
        );

        $keys = array_filter(array_map(
            static fn (string $value): string => strtolower(trim($value)),
            explode(',', (string) $raw)
        ));

        $mapped = [];
        foreach ($keys as $key) {
            $mapped[$key] = true;
        }

        $this->keys = $mapped;
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    public function __invoke(array $record): array
    {
        if (isset($record['context'])) {
            $record['context'] = $this->redactValue($record['context']);
        }

        if (isset($record['extra'])) {
            $record['extra'] = $this->redactValue($record['extra']);
        }

        return $record;
    }

    private function redactValue(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $redacted = [];
        foreach ($value as $key => $item) {
            if (is_string($key) && $this->shouldRedactKey($key)) {
                $redacted[$key] = '[REDACTED]';
                continue;
            }

            $redacted[$key] = $this->redactValue($item);
        }

        return $redacted;
    }

    private function shouldRedactKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach ($this->keys as $needle => $enabled) {
            if ($enabled && str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }
}
