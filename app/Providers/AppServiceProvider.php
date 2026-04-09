<?php

namespace App\Providers;

use App\Services\PushNotificationService;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Blade;
use App\Models\FeatureToggle;
use App\Models\EmailLog;
use App\Models\User;
use App\Services\Email\EmailNotificationService;
use App\Services\NavigationService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share('exceptionAsMarkdown', false);

        Gate::before(function ($user, $ability) {
            if ($user instanceof \App\Models\Admin && $user->is_super_admin) {
                return true;
            }
        });

        Blade::if('feature', function (string $featureName) {
            return FeatureToggle::isActive($featureName);
        });

        Event::listen(Registered::class, function (Registered $event) {
            if (app()->runningInConsole()) {
                return;
            }

            if (!$event->user instanceof User) {
                return;
            }

            app(EmailNotificationService::class)->sendWelcome($event->user);
            app(PushNotificationService::class)->sendWelcome($event->user);
        });

        Event::listen(Login::class, function (Login $event) {
            if (app()->runningInConsole()) {
                return;
            }

            if (!$event->user instanceof User) {
                return;
            }

            $ip = (string) request()->ip();
            $ua = request()->userAgent();
            $at = now()->toIso8601String();

            app(EmailNotificationService::class)->sendLoginAlert($event->user, $ip, $ua, $at);
            app(PushNotificationService::class)->sendLoginAlert($event->user, $ip, $ua, $at);
        });

        Event::listen(MessageSent::class, function (MessageSent $event) {
            if (!Schema::hasTable('email_logs')) {
                return;
            }

            $original = $event->sent->getOriginalMessage();
            $headers = $original->getHeaders();
            $logHeader = $headers->get('X-Email-Log-Id');

            if (!$logHeader) {
                return;
            }

            $logId = trim((string) $logHeader->getBody());
            if ($logId === '') {
                return;
            }

            $messageId = $event->sent->getMessageId();

            EmailLog::query()
                ->where('id', $logId)
                ->update([
                    'status' => 'sent',
                    'provider_message_id' => $messageId,
                    'sent_at' => now(),
                ]);
        });

        View::composer('layouts.admin', function ($view) {
            $navigationService = app(NavigationService::class);
            $view->with('adminNavigation', $navigationService->getAdminNavigation());
        });
    }
}
