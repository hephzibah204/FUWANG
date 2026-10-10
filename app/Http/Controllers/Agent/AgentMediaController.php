<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentMediaController extends Controller
{
    /**
     * Safely serve uploaded agent media (passport photos, KYC documents, proofs, issue attachments)
     * without 403 Forbidden web server / symlink restrictions.
     */
    public function show(Request $request, string $path)
    {
        // 1. Sanitize against directory traversal & null-byte injection
        $cleanPath = str_replace(["\0", "\r", "\n"], '', $path);
        $cleanPath = ltrim(str_replace('\\', '/', $cleanPath), '/');

        // Block directory traversal attempts
        if (str_contains($cleanPath, '..') || str_contains($cleanPath, './')) {
            abort(403, 'Invalid media path.');
        }

        // Whitelist allowed subdirectories to ensure security
        $allowedPrefixes = [
            'agent_kyc/',
            'agent_licenses/',
            'agent_issues/',
            'posts/',
            'logistics/',
            'tickets/',
            'proof_of_address/',
            'notary/',
            'branding/',
            'stamps/',
            'avatars/',
            'admin_avatars/',
        ];

        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($cleanPath, $prefix)) {
                $isAllowed = true;
                break;
            }
        }

        // Also allow single-level filename matches with safe characters & extensions
        if (!$isAllowed && preg_match('/^[a-zA-Z0-9_\-\/]+\.(jpg|jpeg|png|webp|gif|svg|pdf)$/i', $cleanPath)) {
            $isAllowed = true;
        }

        if (!$isAllowed) {
            abort(403, 'Unauthorized access to media path.');
        }

        // 2. Resolve physical file path across potential storage roots
        $candidates = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            storage_path('app/' . $cleanPath),
            public_path($cleanPath),
        ];

        $fullPath = null;
        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && !is_dir($candidate)) {
                $fullPath = $candidate;
                break;
            }
        }

        // 3. Fallback for images if file is missing (e.g. freshly seeded DB, demo, or missing file)
        if (!$fullPath) {
            if ($this->isImage($cleanPath)) {
                return $this->serveDefaultAvatar();
            }
            abort(404, 'Requested document or image not found.');
        }

        // 4. Determine MIME type
        $mime = $this->detectMimeType($fullPath);

        // 5. Stream the response inline
        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    protected function isImage(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);
    }

    protected function detectMimeType(string $file): string
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
        ];

        if (isset($mimes[$ext])) {
            return $mimes[$ext];
        }

        return @mime_content_type($file) ?: 'application/octet-stream';
    }

    protected function serveDefaultAvatar(): Response
    {
        // SVG default avatar representation so UI never breaks
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="120" height="120">'
             . '<rect width="120" height="120" fill="#1e293b"/>'
             . '<circle cx="60" cy="45" r="24" fill="#64748b"/>'
             . '<path d="M20 105 C20 80, 42 75, 60 75 C78 75, 100 80, 100 105 Z" fill="#64748b"/>'
             . '</svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Mirror a newly stored file into public/storage if it's a physical directory.
     */
    public static function mirrorToPublicStorage(string $path): void
    {
        try {
            $source = storage_path('app/public/' . $path);
            $target = public_path('storage/' . $path);

            if (file_exists($source) && is_dir(public_path('storage')) && !is_link(public_path('storage'))) {
                $targetDir = dirname($target);
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }
                @copy($source, $target);
            }
        } catch (\Throwable $e) {
            // Safe ignore
        }
    }
}
