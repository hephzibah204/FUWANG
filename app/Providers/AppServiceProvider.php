<?php

namespace App\Providers;

use App\Services\PushNotificationService;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Blade;
use App\Models\FeatureToggle;
use App\Models\EmailLog;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\TransactionObserver;
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
            if (app()->runningInConsole() && !app()->environment('testing')) {
                return;
            }

            if (!$event->user instanceof User) {
                return;
            }

            app(EmailNotificationService::class)->sendWelcome($event->user);
            app(PushNotificationService::class)->sendWelcome($event->user);
        });

        Event::listen(Login::class, function (Login $event) {
            if (app()->runningInConsole() && !app()->environment('testing')) {
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

        View::composer('services.*', function ($view) {
            $data = $view->getData();
            foreach ($data as $key => $value) {
                if ($value instanceof \Illuminate\Database\Eloquent\Collection) {
                    if ($value->isNotEmpty() && $value->first() instanceof \App\Models\CustomApi) {
                        $value->transform(function ($provider) {
                            if (stripos((string) $provider->name, 'vuvaa') !== false || stripos((string) $provider->provider_identifier, 'vuvaa') !== false) {
                                $provider->name = 'Provider 1';
                            } elseif (stripos((string) $provider->name, 'dataverify') !== false || stripos((string) $provider->provider_identifier, 'dataverify') !== false) {
                                $provider->name = 'Provider 2';
                            } else {
                                $hash = crc32((string) $provider->name);
                                $provider->name = 'Provider ' . (($hash % 10) + 3);
                            }
                            return $provider;
                        });
                    }
                }
            }
        });

        try {
            if (Schema::hasTable('api_centers')) {
                $ac = \Illuminate\Support\Facades\DB::table('api_centers')->first();
                if ($ac) {
                    // Register dynamic mailers in memory
                    config([
                        'mail.mailers.resend_smtp' => [
                            'transport' => 'smtp',
                            'host' => 'smtp.resend.com',
                            'port' => 465,
                            'encryption' => 'tls',
                            'username' => 'resend',
                            'password' => $ac->resend_api_key,
                            'timeout' => null,
                            'local_domain' => env('MAIL_EHLO_DOMAIN'),
                        ],
                        'mail.mailers.mailtrap_smtp' => [
                            'transport' => 'smtp',
                            'host' => $ac->mailtrap_host ?? 'send.smtp.mailtrap.io',
                            'port' => $ac->mailtrap_port ?? 587,
                            'encryption' => 'tls',
                            'username' => $ac->mailtrap_username ?? 'api',
                            'password' => $ac->mailtrap_password,
                            'timeout' => null,
                            'local_domain' => env('MAIL_EHLO_DOMAIN'),
                        ],
                        'mail.mailers.hostinger_smtp' => [
                            'transport' => 'smtp',
                            'host' => env('MAIL_HOST', 'smtp.hostinger.com'),
                            'port' => env('MAIL_PORT', 465),
                            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                            'username' => env('MAIL_USERNAME'),
                            'password' => env('MAIL_PASSWORD'),
                            'timeout' => null,
                            'local_domain' => env('MAIL_EHLO_DOMAIN'),
                        ],
                        'mail.mailers.failover' => [
                            'transport' => 'failover',
                            'mailers' => ['resend_smtp', 'mailtrap_smtp', 'hostinger_smtp'],
                        ],
                        'mail.mailers.roundrobin' => [
                            'transport' => 'roundrobin',
                            'mailers' => ['resend_smtp', 'mailtrap_smtp', 'hostinger_smtp'],
                        ],
                    ]);

                    // Determine active mailer from Admin selection
                    $active = $ac->active_mailer ?? 'failover';
                    
                    if ($active === 'resend') {
                        config(['mail.default' => 'resend_smtp']);
                    } elseif ($active === 'mailtrap') {
                        config(['mail.default' => 'mailtrap_smtp']);
                    } elseif ($active === 'hostinger') {
                        config(['mail.default' => 'hostinger_smtp']);
                    } elseif ($active === 'roundrobin') {
                        config(['mail.default' => 'roundrobin']);
                    } else {
                        config(['mail.default' => 'failover']);
                    }
                }
            }
        } catch (\Exception $e) {
            // Safe ignore
        }

        Transaction::observe(TransactionObserver::class);
    }
}
