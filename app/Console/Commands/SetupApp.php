<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class SetupApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup {--force : Override existing installation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the application from CLI (Recommended over web installer)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $installedFile = storage_path('app/installed');
        if (file_exists($installedFile) && !$this->option('force')) {
            $this->error('Application is already marked as installed. Use --force to override.');
            return 1;
        }

        $this->info('Starting FUWA Platform Setup...');

        // 1. Check/Generate App Key
        if (empty(config('app.key'))) {
            $this->info('Generating Application Key...');
            Artisan::call('key:generate');
        }

        // 2. Database Connectivity
        $this->info('Verifying database connection...');
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->error('Database connection failed: ' . $e->getMessage());
            $this->comment('Please configure your .env file with correct DB_ credentials before running this command.');
            return 1;
        }

        // 3. Run Migrations & Import Schema
        $this->info('Running database migrations and importing schema...');
        try {
            Artisan::call('db:import-schema', ['--silent' => true]);
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            $this->error('Database setup failed: ' . $e->getMessage());
            return 1;
        }

        // 4. Create Admin
        if (Admin::count() === 0 || $this->confirm('Create a new super admin user?')) {
            $username = $this->ask('Admin Username', 'superadmin');
            $email = $this->ask('Admin Email', 'admin@' . parse_url(config('app.url'), PHP_URL_HOST) ?: 'fuwa.ng');
            $password = $this->secret('Admin Password (min 8 chars)');
            
            if (strlen($password) < 8) {
                $this->error('Password too short.');
                return 1;
            }

            Admin::create([
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_super_admin' => true,
            ]);
            $this->info("Super admin '{$username}' created.");
        }

        // 5. Create Installed marker
        file_put_contents($installedFile, 'Installed on ' . now());
        $this->info('Application setup complete. installer is now disabled for security.');

        return 0;
    }
}
