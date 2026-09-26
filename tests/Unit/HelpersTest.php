<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_youtube_links_are_converted_to_embed_urls(): void
    {
        $this->assertSame('https://www.youtube.com/embed/abc_123', youtube_embed_url('https://www.youtube.com/watch?v=abc_123'));
        $this->assertSame('https://www.youtube.com/embed/xyz', youtube_embed_url(' https://youtu.be/xyz '));
        $this->assertSame('https://vimeo.com/1', youtube_embed_url('https://vimeo.com/1'));
        $this->assertSame('', youtube_embed_url(''));
    }
}
