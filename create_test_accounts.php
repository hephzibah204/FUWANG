<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$admin = Admin::where('email', 'admin@fuwa.ng')->first();
if (!$admin) {
    $admin = Admin::create([
        'username' => 'admin',
        'email' => 'admin@fuwa.ng',
        'password' => Hash::make('password123'),
        'role' => 'admin',
        'is_super_admin' => true,
    ]);
    echo "Admin created: admin@fuwa.ng / password123\n";
} else {
    echo "Admin exists: admin@fuwa.ng / password123 (assuming)\n";
}

$user = User::where('email', 'user@fuwa.ng')->first();
if (!$user) {
    $user = User::create([
        'fullname' => 'Test User',
        'username' => 'testuser',
        'email' => 'user@fuwa.ng',
        'password' => Hash::make('password123'),
        'number' => '08012345678',
    ]);
    $user->balance()->create(['user_balance' => 5000]);
    echo "User created: user@fuwa.ng / password123\n";
} else {
    echo "User exists: user@fuwa.ng / password123 (assuming)\n";
}
