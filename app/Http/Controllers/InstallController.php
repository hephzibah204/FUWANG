<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\Admin;

class InstallController extends Controller
{
    public function index()
    {
        $requirements = [
            'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'BCMath PHP Extension' => extension_loaded('bcmath'),
            'Ctype PHP Extension' => extension_loaded('ctype'),
            'JSON PHP Extension' => extension_loaded('json'),
            'Mbstring PHP Extension' => extension_loaded('mbstring'),
            'OpenSSL PHP Extension' => extension_loaded('openssl'),
            'PDO PHP Extension' => extension_loaded('pdo'),
            'Tokenizer PHP Extension' => extension_loaded('tokenizer'),
            'XML PHP Extension' => extension_loaded('xml'),
            '.env Writable' => is_writable(base_path('.env')) || is_writable(base_path()),
            'storage Writable' => is_writable(storage_path()),
            'bootstrap/cache Writable' => is_writable(base_path('bootstrap/cache')),
        ];

        $allRequirementsMet = !in_array(false, $requirements);

        return view('install.index', compact('requirements', 'allRequirementsMet'));
    }

    public function setupDatabase(Request $request)
    {
        $request->validate([
            'db_connection' => 'required|in:mysql,pgsql,sqlite,sqlsrv',
            'db_host' => 'required_unless:db_connection,sqlite',
            'db_port' => 'required_unless:db_connection,sqlite',
            'db_database' => 'required',
            'db_username' => 'required_unless:db_connection,sqlite',
        ], [
            'db_connection.required' => 'Please select a database type.',
            'db_host.required_unless' => 'The database host is required (usually 127.0.0.1 or localhost).',
            'db_port.required_unless' => 'The database port is required (usually 3306 for MySQL).',
            'db_database.required' => 'The database name is required. If using SQLite, this is the file path.',
            'db_username.required_unless' => 'The database username is required to connect to the database.',
        ]);

        try {
            $this->setEnv([
                'DB_CONNECTION' => $request->db_connection,
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_database,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password ?? '',
            ]);

            // Update config dynamically for the current request
            config([
                'database.default' => $request->db_connection,
                "database.connections.{$request->db_connection}.host" => $request->db_host,
                "database.connections.{$request->db_connection}.port" => $request->db_port,
                "database.connections.{$request->db_connection}.database" => $request->db_database,
                "database.connections.{$request->db_connection}.username" => $request->db_username,
                "database.connections.{$request->db_connection}.password" => $request->db_password ?? '',
            ]);

            // Clear config cache to ensure new DB credentials are used for next requests
            Artisan::call('config:clear');

            if ($request->db_connection === 'sqlite') {
                $isAbsolute = str_starts_with($request->db_database, '/') || preg_match('#^[a-zA-Z]:\\\\#', $request->db_database);
                $sqlitePath = $isAbsolute ? $request->db_database : base_path($request->db_database);
                if (!file_exists(dirname($sqlitePath))) {
                    mkdir(dirname($sqlitePath), 0755, true);
                }
                if (!file_exists($sqlitePath)) {
                    touch($sqlitePath);
                }
            }

            // Test connection
            DB::purge();
            
            // Reconfigure database for the current request context before testing
            if ($request->db_connection === 'sqlite') {
                 $sqlitePath = str_starts_with($request->db_database, '/') || preg_match('#^[a-zA-Z]:\\\\#', $request->db_database) 
                    ? $request->db_database 
                    : base_path($request->db_database);
                    
                config([
                    'database.default' => 'sqlite',
                    'database.connections.sqlite.database' => $sqlitePath,
                ]);
            } else {
                config([
                    'database.default' => $request->db_connection,
                    "database.connections.{$request->db_connection}.host" => $request->db_host,
                    "database.connections.{$request->db_connection}.port" => $request->db_port,
                    "database.connections.{$request->db_connection}.database" => $request->db_database,
                    "database.connections.{$request->db_connection}.username" => $request->db_username,
                    "database.connections.{$request->db_connection}.password" => $request->db_password ?? '',
                ]);
            }
            
            try {
                DB::connection()->getPdo();
            } catch (\Exception $e) {
                if ($this->isMissingDatabaseError($e, $request->db_connection)) {
                    $this->tryCreateDatabase($request);
                    DB::purge();
                    DB::connection()->getPdo();
                } else {
                    throw new \Exception("Could not connect to the database. Please check your settings. Error: " . $e->getMessage());
                }
            }

            if ($request->has('_test_only')) {
                return response()->json(['success' => true, 'message' => 'Connection successful']);
            }

            $importExitCode = Artisan::call('db:import-schema', ['--silent' => true]);
            $importOutput = trim(Artisan::output());
            $imported = $importExitCode === 0 && str_contains($importOutput, 'Schema successfully imported');
            $noSqlFound = $importExitCode === 2;

            if (!$noSqlFound && !$imported) {
                throw new \Exception(trim($importOutput) ?: 'SQL schema import failed.');
            }

            $migrateExit = Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = trim(Artisan::output());
            if ($migrateExit !== 0) {
                throw new \Exception($migrateOutput ?: 'Migration failed.');
            }

            $seedOutput = '';
            if ($noSqlFound) {
                Artisan::call('db:seed', ['--force' => true]);
                $seedOutput = trim(Artisan::output());
            }

            if (!Schema::hasTable('users') && !Schema::hasTable('admins')) {
                throw new \Exception('Database setup did not create any tables. Please check your SQL file or migrations.');
            }

            $method = $imported ? 'Imported SQL schema + applied migrations' : 'Applied migrations';
            $fileLine = $this->extractImportFileLine($importOutput);
            $summary = $fileLine ? "{$method}: {$fileLine}" : $method;

            $log = '';
            if (!empty($importOutput)) {
                $log .= "IMPORT:\n" . $importOutput . "\n\n";
            }
            $log .= "MIGRATE:\n" . ($migrateOutput ?: '(no output)') . "\n\n";
            if (!empty($seedOutput)) {
                $log .= "SEED:\n" . $seedOutput . "\n";
            }

            return redirect()
                ->route('install.admin')
                ->with('success', "Database configured successfully. {$summary}")
                ->with('install_log', trim($log));

        } catch (\Exception $e) {
            if ($request->has('_test_only')) {
                return response()->json(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', 'Database connection failed: ' . $e->getMessage());
        }
    }

    public function adminSetup()
    {
        return view('install.admin');
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|alpha_dash',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'admin_path' => 'required|string|max:50|alpha_dash|not_in:login,register,install,api,admin,dashboard',
        ], [
            'site_name.required' => 'Please provide a name for your website.',
            'username.required' => 'An admin username is required.',
            'username.alpha_dash' => 'The username may only contain letters, numbers, dashes, and underscores.',
            'email.required' => 'An admin email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'password.required' => 'A password is required to secure your admin account.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
            'admin_path.required' => 'Please provide a custom path for your admin login.',
            'admin_path.alpha_dash' => 'The admin path may only contain letters, numbers, dashes, and underscores.',
            'admin_path.not_in' => 'The chosen admin path conflicts with system routes. Please choose a different word.',
        ]);

        try {
            Admin::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'super_admin',
                'is_super_admin' => true,
            ]);

            // Save admin path to env
            $this->setEnv([
                'ADMIN_PATH' => ltrim($request->admin_path, '/'),
                'APP_NAME' => $request->site_name,
            ]);
            Artisan::call('config:clear');
            Artisan::call('route:clear');

            // Create installed file
            if (!file_exists(storage_path('app'))) {
                mkdir(storage_path('app'), 0755, true);
            }
            file_put_contents(storage_path('app/installed'), 'Installed on ' . now());

            return redirect()->route('install.complete');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create admin: ' . $e->getMessage());
        }
    }

    public function complete()
    {
        return view('install.complete');
    }

    private function setEnv(array $values)
    {
        // For security, automated .env writes via web are disabled.
        // Users should use the 'php artisan app:setup' CLI command or manually edit the .env file.
        \Illuminate\Support\Facades\Log::info('Automated .env write attempted but blocked for security.', $values);
    }

    private function isMissingDatabaseError(\Exception $e, string $connection): bool
    {
        $msg = strtolower($e->getMessage());
        if ($connection === 'mysql') {
            return str_contains($msg, 'unknown database');
        }
        if ($connection === 'pgsql') {
            return str_contains($msg, 'does not exist');
        }
        return false;
    }

    private function tryCreateDatabase(Request $request): void
    {
        if ($request->db_connection === 'mysql') {
            $dsn = "mysql:host={$request->db_host};port={$request->db_port};charset=utf8mb4";
            $pdo = new \PDO($dsn, $request->db_username, $request->db_password ?? '', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $dbName = str_replace('`', '``', $request->db_database);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            return;
        }

        if ($request->db_connection === 'pgsql') {
            $dsn = "pgsql:host={$request->db_host};port={$request->db_port};dbname=postgres";
            $pdo = new \PDO($dsn, $request->db_username, $request->db_password ?? '', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $dbName = preg_replace('/[^a-zA-Z0-9_]/', '_', $request->db_database);
            $pdo->exec("CREATE DATABASE \"{$dbName}\"");
        }
    }

    private function extractImportFileLine(string $output): ?string
    {
        if (empty($output)) {
            return null;
        }
        $plain = strip_tags($output);
        foreach (preg_split("/\r\n|\n|\r/", $plain) as $line) {
            $line = trim($line);
            if (str_starts_with($line, 'Found SQL file:')) {
                return trim(str_replace('Found SQL file:', '', $line));
            }
        }
        return null;
    }
}
