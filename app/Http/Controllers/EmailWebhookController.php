<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use App\Models\EmailWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EmailWebhookController extends Controller
{
    public function handle(Request $request, string $provider)
    {
        $secret = (string) config('services.email_webhooks.secret', '');
        if ($secret === '') {
            abort(404);
        }

        $sig = (string) $request->header('X-Webhook-Secret', '');
        if (!hash_equals($secret, $sig)) {
            abort(403);
        }

        $payload = $request->all();

        $eventType = (string) ($payload['event'] ?? $payload['type'] ?? $payload['event_type'] ?? 'unknown');
        $messageId = (string) ($payload['message_id'] ?? $payload['Message-Id'] ?? $payload['message-id'] ?? $payload['messageId'] ?? '');
        $recipient = (string) ($payload['recipient'] ?? $payload['email'] ?? $payload['to'] ?? '');

        if (Schema::hasTable('email_webhook_events')) {
            EmailWebhookEvent::query()->create([
                'provider' => $provider,
                'event_type' => $eventType,
                'message_id' => $messageId,
                'recipient' => $recipient,
                'payload' => $payload,
            ]);
        }

        if ($messageId !== '' && Schema::hasTable('email_logs')) {
            $newStatus = null;

            $lower = strtolower($eventType);
            if (str_contains($lower, 'bounce') || str_contains($lower, 'bounced')) {
                $newStatus = 'bounced';
            } elseif (str_contains($lower, 'complaint') || str_contains($lower, 'spam')) {
                $newStatus = 'complaint';
            } elseif (str_contains($lower, 'delivered')) {
                $newStatus = 'delivered';
            }

            if ($newStatus) {
                EmailLog::query()
                    ->where('provider_message_id', $messageId)
                    ->update([
                        'status' => $newStatus,
                        'metadata' => $payload,
                    ]);
            }
        }

        return response()->json(['ok' => true]);
    }
}

