<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$login = '081111111111';
$password = 'jokowiikn123';

$loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
$user = User::where($loginField, $login)->first();

if (!$user) {
    echo "User not found\n";
    exit;
}

echo "User found: " . $user->name . " (Role: " . $user->role . ")\n";

if (Hash::check($password, $user->password)) {
    echo "Password correct!\n";
} else {
    echo "Password incorrect!\n";
}
