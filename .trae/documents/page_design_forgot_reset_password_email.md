# Page Design Spec — Forgot/Reset Password (Nexus styling)

## Global (applies to all pages)
- Layout
  - Desktop-first centered auth panel: `max-width: 420–480px`, vertically centered with safe top/bottom padding.
  - Use Flexbox for centering + stacked form controls; consistent 12/16/24 spacing scale.
- Meta Information
  - Default title format: `Nexus — {Page}`
  - Default description: short, task-specific (e.g., “Request a password reset link”).
  - Open Graph: `og:title`, `og:description`, `og:type=website`.
- Global Styles
  - Use existing Nexus tokens/components (do not introduce new visual language).
  - Buttons: primary (filled), secondary (text/ghost). Hover/focus states must match existing.
  - Inputs: same label, helper text, error state, and focus ring as current auth forms.
  - Alerts/toasts: reuse current success/error components.

---

## 1) Sign-in (existing page — additive change only)
- Page Structure
  - Keep existing sign-in card and layout.
- Sections & Components
  - “Forgot password?” text link beneath password field (right-aligned or below form actions, matching existing patterns).
  - Optional helper line under the link: “We’ll email you a reset link.” (only if consistent with current copy density).
- Interaction States
  - Link uses standard Nexus link style; keyboard focus visible.

---

## 2) Forgot password
- Meta Information
  - Title: `Nexus — Forgot password`
  - Description: `Request a password reset link via email.`
- Page Structure
  - Centered card with: header, instructions, single input form, actions, footer navigation.
- Sections & Components
  1. Header
     - Title: “Forgot your password?”
     - Subtitle: “Enter your email and we’ll send a reset link.”
  2. Form
     - Email input (label + placeholder).
     - Helper text area reserved for validation errors.
     - Primary button: “Send reset link”.
  3. Confirmation state (after submit)
     - Success alert within the same card: “If an account exists for that email, you’ll receive a link shortly.”
     - Secondary button/link: “Back to sign in”.
     - Resend link/button: “Resend email” with cooldown timer text (e.g., “Try again in 30s”).
  4. Error state (network/provider failure)
     - Inline error alert with retry CTA; keep copy generic.
- Responsive behavior
  - On narrow widths, card becomes full-width with 16px side padding; buttons become full-width.

---

## 3) Reset password
- Meta Information
  - Title: `Nexus — Reset password`
  - Description: `Set a new password for your account.`
- Page Structure
  - Centered card with token-handling gate, then password form.
- Sections & Components
  1. Token validation gate
     - Loading state while parsing/verifying token.
     - Invalid/expired state:
       - Alert: “This reset link is invalid or expired.”
       - CTA: “Request a new link” → /forgot-password
  2. Password form (only when token is valid)
     - New password input + confirm password input.
     - Password rules helper text (short bullet list) and mismatch error.
     - Primary button: “Update password”.
  3. Completion state
     - Success alert: “Your password has been updated.”
     - Primary CTA: “Continue to sign in” → /login
- Interaction States
  - Disable submit while processing; show inline spinner consistent with Nexus.
  - Form errors appear inline under fields; keep page-level alert for token issues only.
