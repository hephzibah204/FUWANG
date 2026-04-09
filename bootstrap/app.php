<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware) {
        $trustedProxiesRaw = (string) (env('TRUSTED_PROXIES') ?? '');
        $trustedProxies = null;
        if (trim($trustedProxiesRaw) !== '') {
            $parts = array_values(array_filter(array_map('trim', explode(',', $trustedProxiesRaw))));
            $trustedProxies = count($parts) === 1 ? $parts[0] : $parts;
        }
        $middleware->trustProxies(at: $trustedProxies);

        $middleware->append(\App\Http\Middleware\ErrorHandlingMiddleware::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\EnforceHttps::class);
        $middleware->append(\App\Http\Middleware\ContentSecurityPolicy::class);
        $middleware->append(\App\Http\Middleware\ActivityLogMiddleware::class);
        $middleware->append(\PragmaRX\Google2FALaravel\Middleware::class);

        $installerEnabled = filter_var(env('INSTALLER_ENABLED', false), FILTER_VALIDATE_BOOL) && app()->environment(['local', 'testing']);
        if ($installerEnabled) {
            $middleware->append(\App\Http\Middleware\CheckInstallation::class);
        }

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'payvessel_webhook.php',
            'palmpay_webhook.php',
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            $adminPath = config('app.admin_path', 'admin');
            if ($request->is($adminPath) || $request->is("{$adminPath}/*")) {
                return route('admin.login');
            }
            return route('login');
        });
        
        $middleware->alias([
            'google2fa' => \PragmaRX\Google2FALaravel\Middleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'permission' => \App\Http\Middleware\CheckAdminPermission::class,
            'feature' => \App\Http\Middleware\CheckFeatureToggle::class,
            'admin.security' => \App\Http\Middleware\AdminSecurityMiddleware::class,
            'admin.audit' => \App\Http\Middleware\AdminAuditMiddleware::class,
            'api.token' => \App\Http\Middleware\ApiTokenAuth::class,
            'api.ratelimit' => \App\Http/Middleware\ApiRateLimit::class,
            'service.ratelimit' => \App\Http\Middleware\ServiceApiRateLimit::class,
            'kyc.enforce' => \App\Http\Middleware\EnforceKycTierLimits::class,
            'ab' => \App\Http\Middleware\AssignAbVariants::class,
            'track.view' => \App\Http\Middleware\LogPageView::class,
            'onboarding' => \App\Http\Middleware\OnboardingMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        \App\Support\Exceptions\ErrorHandler::register($exceptions);
    })->create();
