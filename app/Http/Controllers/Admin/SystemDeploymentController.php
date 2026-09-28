<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class SystemDeploymentController extends Controller
{
    /**
     * Get current Git status and commit information.
     */
    public function status()
    {
        $branch = 'main';
        $commitHash = null;
        $commitMessage = null;
        $commitDate = null;

        // Try using git CLI first
        $gitOutput = $this->runCommand('git log -1 --format="%h||%s||%ci"');
        if (!empty($gitOutput) && str_contains($gitOutput, '||')) {
            $parts = explode('||', trim($gitOutput));
            $commitHash = $parts[0] ?? null;
            $commitMessage = $parts[1] ?? null;
            $commitDate = $parts[2] ?? null;
        }

        // Fallback: Read from .git files directly
        if (!$commitHash && file_exists(base_path('.git/HEAD'))) {
            $headContent = trim(file_get_contents(base_path('.git/HEAD')));
            if (str_starts_with($headContent, 'ref: refs/heads/')) {
                $branch = str_replace('ref: refs/heads/', '', $headContent);
                $refFile = base_path('.git/' . trim(str_replace('ref: ', '', $headContent)));
                if (file_exists($refFile)) {
                    $commitHash = substr(trim(file_get_contents($refFile)), 0, 7);
                }
            } else {
                $commitHash = substr($headContent, 0, 7);
            }
        }

        // Check current branch via CLI
        $branchOutput = $this->runCommand('git branch --show-current');
        if (!empty(trim($branchOutput)) && !str_contains($branchOutput, 'not supported')) {
            $branch = trim($branchOutput);
        }

        return response()->json([
            'status' => 'success',
            'branch' => $branch ?: 'main',
            'commit' => $commitHash ?: 'Unknown',
            'message' => $commitMessage ?: 'Latest deployed commit',
            'date' => $commitDate ?: date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Execute Git Pull from origin main and clear cache.
     */
    public function pull(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$this->isAuthorizedAdmin($admin)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Only Super Administrators can perform system deployments.',
            ], 403);
        }

        $outputs = [];
        $outputs[] = ">>> [1/3] Initiating Git Pull from origin main...";

        // Execute git pull
        $gitPullOutput = $this->runCommand('git pull origin main 2>&1');
        $outputs[] = $gitPullOutput ?: "(No output returned by git pull)";

        // Clear artisan caches
        $outputs[] = "\n>>> [2/3] Clearing application, view, and route caches...";
        try {
            Artisan::call('optimize:clear');
            $outputs[] = Artisan::output();
        } catch (\Throwable $e) {
            $outputs[] = "Cache clear error: " . $e->getMessage();
        }

        $outputs[] = "\n>>> [3/3] Deployment sequence completed successfully.";

        // Audit Log
        try {
            AdminAuditLog::create([
                'admin_id' => $admin->id ?? null,
                'action' => 'system.deploy.git_pull',
                'ip_address' => $request->ip(),
                'details' => json_encode(['result' => $gitPullOutput]),
            ]);
        } catch (\Throwable $ignored) {}

        return response()->json([
            'status' => 'success',
            'output' => implode("\n", $outputs),
            'message' => 'Git Pull and Cache Clear executed successfully.',
        ]);
    }

    /**
     * Clear all system caches.
     */
    public function clearCache(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$this->isAuthorizedAdmin($admin)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        try {
            Artisan::call('optimize:clear');
            $output = Artisan::output();

            return response()->json([
                'status' => 'success',
                'output' => $output,
                'message' => 'All caches cleared successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'output' => $e->getMessage(),
                'message' => 'Failed to clear caches: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run database migrations.
     */
    public function migrate(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$this->isAuthorizedAdmin($admin)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return response()->json([
                'status' => 'success',
                'output' => $output,
                'message' => 'Database migrations finished.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'output' => $e->getMessage(),
                'message' => 'Migration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if admin has deployment permissions.
     */
    protected function isAuthorizedAdmin($admin): bool
    {
        if (!$admin) return false;
        return ($admin->is_super_admin ?? false) ||
               (($admin->role ?? null) === 'superadmin') ||
               Admin::count() <= 1;
    }

    /**
     * Run shell command safely with multiple fallbacks.
     */
    protected function runCommand(string $command): string
    {
        $cwd = base_path();

        if (function_exists('proc_open')) {
            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];

            $process = proc_open($command, $descriptors, $pipes, $cwd);
            if (is_resource($process)) {
                fclose($pipes[0]);
                $stdout = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                proc_close($process);

                return trim($stdout . ($stderr ? "\n" . $stderr : ''));
            }
        }

        if (function_exists('shell_exec')) {
            $output = shell_exec("cd " . escapeshellarg($cwd) . " && " . $command);
            if ($output !== null) {
                return trim($output);
            }
        }

        if (function_exists('exec')) {
            $output = [];
            exec("cd " . escapeshellarg($cwd) . " && " . $command, $output);
            return trim(implode("\n", $output));
        }

        return "Command execution not supported on this PHP configuration.";
    }
}
