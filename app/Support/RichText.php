<?php

namespace App\Support;

use DOMDocument;
use DOMNode;

/**
 * Rich text handling for admin broadcast messages (owner request
 * 2026-11-28: Word-style formatting in broadcasts).
 *
 * sanitize() runs an allowlist over admin-authored HTML before it is
 * stored, emailed or rendered: formatting tags (bold, italic, headings,
 * lists, links) survive; scripts, styles, event handlers, images and
 * every other tag/attribute are removed. Links keep only http, https
 * and mailto hrefs.
 */
class RichText
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
        'ul', 'ol', 'li', 'a', 'h2', 'h3', 'h4', 'blockquote', 'span', 'div',
    ];

    /** Tags removed together with their contents (never unwrapped). */
    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input',
        'button', 'textarea', 'select', 'link', 'meta', 'title', 'img',
        'svg', 'math', 'video', 'audio', 'source', 'track', 'noscript',
    ];

    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        // Plain text with no markup at all: keep it as-is (line breaks
        // are handled by the renderers, same as legacy messages).
        if (!str_contains($html, '<')) {
            return $html;
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><div id="richtext-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $root = $doc->getElementById('richtext-root');
        if (!$root) {
            return strip_tags($html);
        }

        self::cleanNode($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private static function cleanNode(DOMNode $node): void
    {
        // Snapshot: the child list mutates while we work.
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                continue;
            }
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->removeChild($child);
                continue;
            }

            // Clean descendants first so unwrapping keeps cleaned content.
            self::cleanNode($child);

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                // Unwrap: replace the element with its children.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            // Strip every attribute except a safe link href.
            $keepHref = null;
            if ($tag === 'a') {
                $href = trim((string) $child->getAttribute('href'));
                if (preg_match('#^(https?://|mailto:)#i', $href) || str_starts_with($href, '/') || str_starts_with($href, '#')) {
                    $keepHref = $href;
                }
            }
            while ($child->attributes && $child->attributes->length > 0) {
                $child->removeAttribute($child->attributes->item(0)->nodeName);
            }
            if ($keepHref !== null) {
                $child->setAttribute('href', $keepHref);
                $child->setAttribute('rel', 'noopener');
            } elseif ($tag === 'a') {
                // Unsafe or empty href: unwrap the link, keep its text.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
            }
        }
    }

    /** Plain-text version for excerpts, previews and text fallbacks. */
    public static function toPlainText(?string $html): string
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    public static function isHtml(?string $value): bool
    {
        return str_contains((string) $value, '<');
    }
}
