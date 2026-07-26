<?php

namespace Tests\Unit;

use App\YoutubeUrlParser;
use PHPUnit\Framework\TestCase;

class YoutubeUrlParserTest extends TestCase
{
    public function test_it_returns_an_eleven_character_reference_for_request_validation(): void
    {
        $this->assertSame('dQw4w9WgXcQ', (new YoutubeUrlParser)->parse('dQw4w9WgXcQ'));
    }

    public function test_it_extracts_the_video_id_from_a_watch_url(): void
    {
        $videoId = (new YoutubeUrlParser)->parse(
            'https://www.youtube.com/watch?feature=shared&v=dQw4w9WgXcQ&t=10',
        );

        $this->assertSame('dQw4w9WgXcQ', $videoId);
    }

    public function test_it_extracts_the_video_id_from_a_mobile_watch_url(): void
    {
        $videoId = (new YoutubeUrlParser)->parse(
            'https://m.youtube.com/watch?v=dQw4w9WgXcQ',
        );

        $this->assertSame('dQw4w9WgXcQ', $videoId);
    }

    public function test_it_extracts_the_video_id_from_a_shorts_url(): void
    {
        $videoId = (new YoutubeUrlParser)->parse(
            'https://www.youtube.com/shorts/dQw4w9WgXcQ',
        );

        $this->assertSame('dQw4w9WgXcQ', $videoId);
    }

    public function test_it_rejects_unsupported_or_malformed_references(): void
    {
        $parser = new YoutubeUrlParser;

        $this->assertNull($parser->parse('not-a-video-id'));
        $this->assertNull($parser->parse('https://youtube.com.evil.test/watch?v=dQw4w9WgXcQ'));
        $this->assertNull($parser->parse('https://www.youtube.com/watch'));
        $this->assertNull($parser->parse('https://www.youtube.com/not-shorts/dQw4w9WgXcQ'));
    }
}
