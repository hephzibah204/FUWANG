# Email Notifications

## What this adds

- Automated welcome email when a new user registers.
- Automated login alert email when a user logs in.
- HTML + plain text versions for each email.
- Branded layout driven by `SystemSetting` values.
- Unsubscribe links (signed URLs) and `List-Unsubscribe` headers.
- Email delivery logging (`queued`, `sent`, `failed`, optional webhook events).

## Automation

Automation is event-driven:

- `Illuminate\Auth\Events\Registered` triggers the welcome email.
- `Illuminate\Auth\Events\Login` triggers the login notification email.

The handlers are registered in `AppServiceProvider`.

## Data included

- Welcome: user name, dashboard CTA.
- Login alert: user name, login timestamp (ISO8601), IP address, user agent.

## Preferences and unsubscribe

Preferences are stored per user in `email_preferences`:

- `welcome_enabled`
- `login_alerts_enabled`
- `unsubscribed_at`

Unsubscribe links are signed and scoped:

- `scope=login` disables login alerts.
- `scope=all` disables both welcome and login alerts.

## Logging and tracking

Outgoing emails are logged in `email_logs`.

- A unique `X-Email-Log-Id` header is attached to each outgoing mail.
- When the message is sent, `MessageSent` updates the log with `sent_at` and `provider_message_id`.
- If a queued mail fails, the mailable `failed()` method marks it as `failed`.

## Optional bounce/complaint webhooks

An optional provider-agnostic webhook endpoint exists:

- `POST /api/webhooks/email/{provider}`

It requires `services.email_webhooks.secret` and a request header `X-Webhook-Secret`.
If a payload includes a `message_id`, the system will update the matching `email_logs.provider_message_id`.

## Configuration

- Branding:
  - `site_name`
  - `site_logo_url`
  - `contact_email`
  - `contact_address`
- Webhook secret:
  - `EMAIL_WEBHOOK_SECRET`

## Queue

Mailables are queued onto the `emails` queue. Run a queue worker in production.

## Tests

- Unit: template rendering.
- Feature: event-driven automation and DB logging.
- Performance: `performance` group test that queues many emails quickly.

