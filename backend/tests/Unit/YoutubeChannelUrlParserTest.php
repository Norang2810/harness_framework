<?php

namespace Tests\Unit;

use App\Support\YoutubeChannelUrlParser;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class YoutubeChannelUrlParserTest extends TestCase
{
    public function test_it_parses_a_youtube_handle_url(): void
    {
        $parsed = YoutubeChannelUrlParser::parse('https://www.youtube.com/@acme-studio/videos');

        $this->assertSame('handle', $parsed['type']);
        $this->assertSame('@acme-studio', $parsed['value']);
        $this->assertSame('https://www.youtube.com/@acme-studio', $parsed['canonical_url']);
    }

    public function test_it_parses_a_youtube_channel_id_url(): void
    {
        $parsed = YoutubeChannelUrlParser::parse('https://youtube.com/channel/UC1234567890abcdef');

        $this->assertSame('channel', $parsed['type']);
        $this->assertSame('UC1234567890abcdef', $parsed['value']);
        $this->assertSame('https://www.youtube.com/channel/UC1234567890abcdef', $parsed['canonical_url']);
    }

    public function test_it_parses_custom_and_user_urls(): void
    {
        $custom = YoutubeChannelUrlParser::parse('https://www.youtube.com/c/AcmeStudio');
        $user = YoutubeChannelUrlParser::parse('https://www.youtube.com/user/AcmeUser');

        $this->assertSame('c', $custom['type']);
        $this->assertSame('AcmeStudio', $custom['value']);
        $this->assertSame('https://www.youtube.com/c/AcmeStudio', $custom['canonical_url']);
        $this->assertSame('user', $user['type']);
        $this->assertSame('AcmeUser', $user['value']);
        $this->assertSame('https://www.youtube.com/user/AcmeUser', $user['canonical_url']);
    }

    public function test_it_rejects_urls_that_are_not_youtube_channels(): void
    {
        $this->expectException(ValidationException::class);

        YoutubeChannelUrlParser::parse('https://example.com/@acme');
    }
}
