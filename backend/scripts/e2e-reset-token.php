<?php

declare(strict_types=1);

use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Password;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? 'resetuser@example.com';
$user = User::query()->where('email', $email)->first();

if (! $user) {
    fwrite(STDERR, "User not found: {$email}\n");
    exit(1);
}

echo Password::broker()->createToken($user);
