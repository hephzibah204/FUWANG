# LegalDraftingService (Unified Drafting Pipeline)

## Overview
The platform previously had two distinct legal drafting pipelines:
- `App\Services\GeminiService::draftLegalDocument(...)`
- `App\Http\Controllers\AiController::draftLegalDocument(...)`

These have been unified behind:
- `App\Services\LegalDrafting\LegalDraftingService`

This provides a consistent internal API, isolates provider selection, and enables incremental migration of legacy callers.

## Internal API

### Request DTO
- `App\Services\LegalDrafting\LegalDraftRequest`
  - `documentType` (string)
  - `category` (string)
  - `formData` (array)
  - `systemPrompt` (nullable string)

### Service
- `LegalDraftingService::draftHtml(LegalDraftRequest $req): array`
  - Returns:
    - `ok` (bool)
    - `provider` (string|null)
    - `html` (string|null)
    - `message` (string|null)

## Provider routing
Current provider order:
1. `GeminiFlashProvider` (Gemini 1.5 Flash via HTTP)
2. `OpenAiChatProvider` (OpenAI Chat Completions via HTTP)
3. `FallbackProvider` (deterministic HTML fallback)

Providers implement:
- `App\Services\LegalDrafting\Providers\DraftingProvider`

## Backward-compatible adapters
Legacy code paths remain callable:
- `AiController::draftLegalDocument(array $data)` now delegates to `LegalDraftingService`.

This allows incremental migration of existing call sites without breaking changes.

## Operational notes
- Configure Gemini via either:
  - `custom_apis` provider with `service_type = gemini_ai` (preferred), or
  - `api_centers.gemini_api_key` / `GEMINI_API_KEY`.
- Configure OpenAI via `OPENAI_API_KEY`.
- Drafting is best-effort: provider failures fall through to the next provider.

