<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$login = 'admin@laundry.com';
$password = 'jokowiikn123';

$loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
$account = User::query()->where($loginField, $login)->first();

if (!$account || !Hash::check($password, $account->password)) {
    echo "Login failed\n";
    exit;
}

echo json_encode([
    'user' => $account,
    'token' => 'dummy_token'
], JSON_PRETTY_PRINT);
