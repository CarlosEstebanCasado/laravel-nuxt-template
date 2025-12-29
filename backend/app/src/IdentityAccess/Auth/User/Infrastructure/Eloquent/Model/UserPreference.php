<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property string $locale
 * @property string $theme
 * @property string $primary_color
 * @property string $neutral_color
 */
class UserPreference extends Model
{
    protected $table = 'user_preferences';

    protected $fillable = [
        'user_id',
        'locale',
        'theme',
        'primary_color',
        'neutral_color',
    ];
}
