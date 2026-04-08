<?php

namespace App\Http\Middleware;

use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuditMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $route = $request->route();
            $routeName = (string) ($route?->getName() ?? '');
            if (!str_starts_with($routeName, 'admin.audit_logs.')) {
                $admin = Auth::guard('admin')->user();
                $meta = [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'route' => $routeName ?: null,
                    'status' => $response->getStatusCode(),
                    'params' => $this->safeArray($route?->parameters() ?? []),
                    'query' => $this->safeArray($request->query()),
                    'input' => $this->safeArray($request->except(['_token', '_method'])),
                ];

                AdminAuditLog::create([
                    'admin_id' => $admin?->id,
                    'action' => $routeName ?: ('admin.' . strtolower($request->method()) . '.' . str_replace('/', '.', $request->path())),
                    'meta' => $meta,
                    'ip' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AdminAuditMiddleware: failed to write audit log', [
                'error'    => $e->getMessage(),
                'route'    => $routeName ?? null,
                'admin_id' => ($admin ?? null)?->id,
                'method'   => $request->method(),
                'path'     => $request->path(),
            ]);
        }

        return $response;
    }

    private function safeArray(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            $key = (string) $k;
            if (preg_match('/password|secret|token|api_key|key/i', $key)) {
                $out[$key] = '***';
                continue;
            }
            if (is_array($v)) {
                $out[$key] = $this->safeArray($v);
                continue;
            }
            if ($v instanceof \Illuminate\Http\UploadedFile) {
                $out[$key] = ['file' => $v->getClientOriginalName(), 'size' => $v->getSize()];
                continue;
            }
            if (is_string($v) && strlen($v) > 2000) {
                $out[$key] = substr($v, 0, 2000);
                continue;
            }
            $out[$key] = $v;
        }
        return $out;
    }
}
