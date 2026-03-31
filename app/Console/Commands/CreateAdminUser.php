<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    protected $signature = 'app:admin-user
        {--email= : Admin email}
        {--username= : Admin username}
        {--password= : Admin password (plain text)}
        {--super : Mark as super admin}
        {--reset : Reset password for existing admin (by email)}';

    protected $description = 'Create or reset an admin user';

    public function handle(): int
    {
        $email = (string) ($this->option('email') ?? '');
        $username = (string) ($this->option('username') ?? '');
        $password = (string) ($this->option('password') ?? '');
        $super = (bool) $this->option('super');
        $reset = (bool) $this->option('reset');

        if ($email === '') {
            $this->error('Missing --email');
            return self::FAILURE;
        }

        $admin = Admin::query()->where('email', $email)->first();

        if ($admin && !$reset) {
            $this->error('Admin already exists. Use --reset to reset password.');
            return self::FAILURE;
        }

        if (!$admin && $username === '') {
            $this->error('Missing --username (required when creating a new admin)');
            return self::FAILURE;
        }

        if ($password === '') {
            $password = (string) ($this->secret('Password') ?? '');
        }

        if ($password === '') {
            $this->error('Missing --password (or provide it interactively when prompted).');
            return self::FAILURE;
        }

        if ($admin) {
            $admin->password = $password;
            if ($super) {
                $admin->is_super_admin = true;
            }
            $admin->save();

            $this->info('Admin password updated.');
            $this->line('Email: ' . $admin->email);
            return self::SUCCESS;
        }

        $admin = Admin::create([
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'is_super_admin' => $super,
        ]);

        $this->info('Admin created.');
        $this->line('Email: ' . $admin->email);
        $this->line('Username: ' . $admin->username);
        $this->line('Login URL: ' . url('/' . config('app.admin_path', 'admin') . '/login'));

        return self::SUCCESS;
    }
}
