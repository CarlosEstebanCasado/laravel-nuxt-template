<?php

declare(strict_types=1);

use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\UserPreference;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? 'test@example.com';
$locale = $argv[2] ?? 'es';
$theme = $argv[3] ?? 'dark';
$primaryColor = $argv[4] ?? 'green';
$neutralColor = $argv[5] ?? 'slate';
$timezone = $argv[6] ?? 'Europe/Madrid';

$user = User::query()->where('email', $email)->first();

if (! $user) {
    fwrite(STDERR, "User not found: {$email}\n");
    exit(1);
}

UserPreference::query()->updateOrCreate(
    ['user_id' => $user->id],
    [
        'locale' => $locale,
        'theme' => $theme,
        'primary_color' => $primaryColor,
        'neutral_color' => $neutralColor,
        'timezone' => $timezone,
    ]
);

echo "OK\n";
