<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomApi;

class UpdateVuvaaCredentials extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'update:vuvaa-credentials'
        . ' {--username= : New VUVAA username (default: pink)}'
        . ' {--password= : New VUVAA password (default: Password)}';

    /**
     * The console command description.
     */
    protected $description = 'Update the VUVAA provider credentials (username and password) in the custom_apis table.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = $this->option('username') ?? 'pink';
        $password = $this->option('password') ?? 'Password';

        $provider = CustomApi::where('provider_identifier', 'vuvaa')->first();
        if (! $provider) {
            $this->error('No VUVAA provider found in custom_apis.');
            return 1;
        }

        // Update the JSON config column (or create if missing)
        $config = $provider->config ?? [];
        $config['username'] = $username;
        $config['password'] = $password;
        $provider->config = $config;
        $provider->save();

        $this->info("VUVAA credentials updated: username='{$username}', password='{$password}'.");
        return 0;
    }
}
?>
