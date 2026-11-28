<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\TestCase;

/**
 * Allowlist sanitiser behind the Word-style broadcast editor
 * (owner request 2026-11-28).
 */
class RichTextTest extends TestCase
{
    public function test_formatting_tags_survive(): void
    {
        $out = RichText::sanitize('<p>Hello <strong>Agents</strong>, <em>please</em> note:</p><ul><li>One</li><li>Two</li></ul><h2>Heading</h2>');

        $this->assertStringContainsString('<strong>Agents</strong>', $out);
        $this->assertStringContainsString('<em>please</em>', $out);
        $this->assertStringContainsString('<li>One</li>', $out);
        $this->assertStringContainsString('<h2>Heading</h2>', $out);
    }

    public function test_scripts_and_handlers_are_removed(): void
    {
        $out = RichText::sanitize('<p onclick="steal()">Hi</p><script>alert(1)</script><style>.x{}</style>');

        $this->assertStringNotContainsString('script', $out);
        $this->assertStringNotContainsString('onclick', $out);
        $this->assertStringNotContainsString('alert', $out);
        $this->assertStringContainsString('Hi', $out);
    }

    public function test_unsafe_links_are_unwrapped_and_safe_links_kept(): void
    {
        $bad = RichText::sanitize('<a href="javascript:alert(1)">Click</a>');
        $this->assertStringNotContainsString('javascript:', $bad);
        $this->assertStringNotContainsString('<a', $bad);
        $this->assertStringContainsString('Click', $bad);

        $good = RichText::sanitize('<a href="https://fuwa.ng/help" class="x" style="color:red">Help page</a>');
        $this->assertStringContainsString('href="https://fuwa.ng/help"', $good);
        $this->assertStringNotContainsString('class=', $good);
        $this->assertStringNotContainsString('style=', $good);
    }

    public function test_images_and_unknown_tags_do_not_survive(): void
    {
        $out = RichText::sanitize('<p>Text</p><img src="https://tracker.example/p.gif"><marquee>Old</marquee>');

        $this->assertStringNotContainsString('<img', $out);
        $this->assertStringNotContainsString('tracker.example', $out);
        $this->assertStringNotContainsString('<marquee>', $out);
        $this->assertStringContainsString('Old', $out);
    }

    public function test_plain_text_passes_through_and_converts_back(): void
    {
        $this->assertSame('Just a plain message.', RichText::sanitize('Just a plain message.'));
        $this->assertSame('Hello Agents, please note', RichText::toPlainText('<p>Hello <strong>Agents</strong>, please note</p>'));
        $this->assertTrue(RichText::isHtml('<p>x</p>'));
        $this->assertFalse(RichText::isHtml('plain'));
    }
}
