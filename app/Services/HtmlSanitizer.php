<?php

namespace App\Services;

class HtmlSanitizer
{
    public function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $prev = libxml_use_internal_errors(true);

        $doc = new \DOMDocument();
        $doc->loadHTML(
            '<!doctype html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>',
            \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $allowedTags = array_fill_keys([
            'a', 'abbr', 'article', 'aside', 'b', 'blockquote', 'br', 'button', 'caption', 'code',
            'div', 'em', 'figure', 'figcaption', 'footer', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'header', 'hr', 'i', 'img', 'label', 'li', 'main', 'nav', 'ol', 'p', 'pre', 'section',
            'small', 'span', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead',
            'tr', 'u', 'ul'
        ], true);

        $blockedTags = array_fill_keys([
            'script', 'iframe', 'object', 'embed', 'link', 'meta', 'base', 'form', 'input', 'textarea',
            'select', 'option', 'style'
        ], true);

        $walker = new \RecursiveIteratorIterator(
            new class($doc->getElementsByTagName('*')) implements \RecursiveIterator {
                private \DOMNodeList $list;
                private int $pos = 0;
                public function __construct(\DOMNodeList $list) { $this->list = $list; }
                public function current(): mixed { return $this->list->item($this->pos); }
                public function key(): mixed { return $this->pos; }
                public function next(): void { $this->pos++; }
                public function rewind(): void { $this->pos = 0; }
                public function valid(): bool { return $this->pos < $this->list->length; }
                public function hasChildren(): bool { return false; }
                public function getChildren(): mixed { return null; }
            },
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $toRemove = [];
        foreach ($walker as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);
            if (isset($blockedTags[$tag])) {
                $toRemove[] = $node;
                continue;
            }

            if (!isset($allowedTags[$tag])) {
                $this->unwrap($node);
                continue;
            }

            $this->sanitizeAttributes($node);
        }

        foreach ($toRemove as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }

        $body = $doc->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '';
        }

        $out = '';
        foreach (iterator_to_array($body->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }

        return $out;
    }

    private function unwrap(\DOMElement $el): void
    {
        $parent = $el->parentNode;
        if (!$parent) {
            return;
        }

        while ($el->firstChild) {
            $parent->insertBefore($el->firstChild, $el);
        }
        $parent->removeChild($el);
    }

    private function sanitizeAttributes(\DOMElement $el): void
    {
        $keep = [];
        foreach ($el->attributes ?? [] as $attr) {
            $name = strtolower($attr->name);
            $value = $attr->value;

            if (str_starts_with($name, 'on')) {
                continue;
            }

            if (str_starts_with($name, 'data-') || str_starts_with($name, 'aria-')) {
                $keep[$name] = $value;
                continue;
            }

            if (in_array($name, ['class', 'id', 'title', 'role', 'width', 'height', 'alt'], true)) {
                $keep[$name] = $value;
                continue;
            }

            if ($name === 'href' || $name === 'src') {
                $v = trim($value);
                $lv = strtolower($v);
                if ($lv === '') {
                    continue;
                }
                if (str_starts_with($lv, 'javascript:') || str_starts_with($lv, 'vbscript:')) {
                    continue;
                }
                $keep[$name] = $v;
                continue;
            }

            if ($name === 'target') {
                $keep[$name] = $value;
                continue;
            }

            if ($name === 'rel') {
                $keep[$name] = $value;
                continue;
            }

            if ($name === 'style') {
                $keep[$name] = $this->sanitizeStyle($value);
                continue;
            }
        }

        while ($el->attributes->length) {
            $el->removeAttributeNode($el->attributes->item(0));
        }

        foreach ($keep as $k => $v) {
            if ($k === 'style' && trim($v) === '') {
                continue;
            }
            $el->setAttribute($k, $v);
        }

        if (strtolower($el->tagName) === 'a') {
            $target = strtolower((string) $el->getAttribute('target'));
            if ($target === '_blank') {
                $rel = strtolower((string) $el->getAttribute('rel'));
                $tokens = array_filter(preg_split('/\s+/', $rel) ?: []);
                foreach (['noopener', 'noreferrer'] as $t) {
                    if (!in_array($t, $tokens, true)) {
                        $tokens[] = $t;
                    }
                }
                $el->setAttribute('rel', trim(implode(' ', $tokens)));
            }
        }
    }

    private function sanitizeStyle(string $style): string
    {
        $s = preg_replace('/[\r\n\t]/', ' ', $style) ?? '';
        $s = preg_replace('/expression\s*\(/i', '', $s) ?? '';
        $s = preg_replace('/url\s*\(\s*(["\']?)\s*javascript\s*:/i', 'url($1', $s) ?? '';
        $s = preg_replace('/javascript\s*:/i', '', $s) ?? '';
        return trim($s);
    }
}

