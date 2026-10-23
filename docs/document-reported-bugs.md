# Document reported bugs

Validation endpoint resolution now reads optional attributes from the API settings row instead of selecting columns that may not exist. Existing overrides and default URLs remain supported.

Agent dashboard and onboarding balances now use the User wallet_balance accessor backed by the balance relationship. The shared header also shows the balance on mobile.

Consent now supports installations missing the profile consent columns: explicit acceptance is retained in the authenticated user's session and recorded durably with each submitted request. The normal profile fields are still updated when present. The browser requires successful acceptance before submission and displays nested server errors. Requests enter the admin modification queue as waiting_for_review, with supporting documents and the original payment reference and amount. Admin rejection uses that payment for refunds.

The modification feature suite passed with 7 tests and 45 assertions, including submission and admin pickup without the profile consent columns. PHP syntax checks and git diff whitespace checks passed. Tests required execution outside the sandbox to read the existing dependencies. These changes still need deployment; clear stale application caches after deploying.
