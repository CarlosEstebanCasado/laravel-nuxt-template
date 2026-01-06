<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $auth_provider
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \Illuminate\Support\Carbon|null $two_factor_confirmed_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $password_set_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable implements AuditableContract, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use AuditableTrait, HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'auth_provider',
        'password_set_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Attributes that should not be audited.
     *
     * @var list<string>
     */
    protected array $auditExclude = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_set_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function transformAudit(array $data): array
    {
        if (($data['event'] ?? null) !== 'updated') {
            return $data;
        }

        $dirty = $this->getDirty();
        $hasSecretChange = array_key_exists('two_factor_secret', $dirty);
        $hasRecoveryChange = array_key_exists('two_factor_recovery_codes', $dirty);
        $hasConfirmedChange = array_key_exists('two_factor_confirmed_at', $dirty);

        if (! $hasSecretChange && ! $hasRecoveryChange && ! $hasConfirmedChange) {
            return $data;
        }

        $original = $this->getOriginal();
        $newValues = is_array($data['new_values'] ?? null) ? $data['new_values'] : [];
        $oldValues = is_array($data['old_values'] ?? null) ? $data['old_values'] : [];

        $isDisabled = $hasSecretChange && ($dirty['two_factor_secret'] ?? null) === null;
        $isConfirmed = $hasConfirmedChange && ($dirty['two_factor_confirmed_at'] ?? null) !== null;
        $isRecoveryRegenerated = $hasRecoveryChange
            && ($original['two_factor_recovery_codes'] ?? null) !== null
            && ($dirty['two_factor_recovery_codes'] ?? null) !== null
            && ! $isDisabled;
        $isEnabled = ! $isDisabled && ! $isConfirmed && $hasSecretChange && ($dirty['two_factor_secret'] ?? null) !== null;

        if ($isDisabled) {
            $newValues['two_factor_disabled'] = true;
        } elseif ($isConfirmed) {
            $newValues['two_factor_confirmed'] = true;
        } elseif ($isEnabled) {
            $newValues['two_factor_enabled'] = true;
        } elseif ($isRecoveryRegenerated) {
            $newValues['two_factor_recovery_codes_regenerated'] = true;
        }

        $data['old_values'] = $oldValues;
        $data['new_values'] = $newValues;

        return $data;
    }
}
