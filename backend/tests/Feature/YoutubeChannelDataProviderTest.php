<?php

namespace Tests\Feature;

use App\Services\YoutubeChannelDataProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class YoutubeChannelDataProviderTest extends TestCase
{
    public function test_it_fetches_channel_metadata_by_handle_without_exposing_the_key(): void
    {
        Config::set('services.youtube.data_api_key', 'youtube-secret');
        Http::fake([
            'https://www.googleapis.com/youtube/v3/channels*' => Http::response([
                'items' => [
                    [
                        'id' => 'UC123',
                        'snippet' => [
                            'title' => 'Acme Studio',
                            'description' => 'Channel description',
                            'publishedAt' => '2024-01-01T00:00:00Z',
                            'customUrl' => '@acme',
                            'thumbnails' => ['default' => ['url' => 'https://img.example/acme.jpg']],
                        ],
                        'statistics' => [
                            'subscriberCount' => '1200',
                            'videoCount' => '42',
                            'viewCount' => '99000',
                        ],
                    ],
                ],
            ]),
        ]);

        $channel = app(YoutubeChannelDataProvider::class)->fetch([
            'type' => 'handle',
            'value' => '@acme',
            'canonical_url' => 'https://www.youtube.com/@acme',
        ]);

        $this->assertSame('UC123', $channel['id']);
        $this->assertSame('Acme Studio', $channel['title']);
        $this->assertSame(1200, $channel['statistics']['subscriber_count']);
        $this->assertSame('https://www.youtube.com/channel/UC123', $channel['canonical_url']);
        $this->assertStringNotContainsString('youtube-secret', json_encode($channel));

        Http::assertSent(function ($request) {
            return $request->method() === 'GET'
                && str_starts_with($request->url(), 'https://www.googleapis.com/youtube/v3/channels')
                && $request['part'] === 'snippet,statistics'
                && $request['forHandle'] === '@acme'
                && $request['key'] === 'youtube-secret';
        });
    }

    public function test_it_reports_missing_youtube_key_as_validation_error(): void
    {
        Config::set('services.youtube.data_api_key', null);

        $this->expectException(ValidationException::class);

        app(YoutubeChannelDataProvider::class)->fetch([
            'type' => 'channel',
            'value' => 'UC123',
            'canonical_url' => 'https://www.youtube.com/channel/UC123',
        ]);
    }

    public function test_it_reports_youtube_connection_failure_as_validation_error(): void
    {
        Config::set('services.youtube.data_api_key', 'youtube-secret');
        Http::fake([
            'https://www.googleapis.com/youtube/v3/channels*' => fn () => throw new ConnectionException('network down'),
        ]);

        $this->expectException(ValidationException::class);

        app(YoutubeChannelDataProvider::class)->fetch([
            'type' => 'channel',
            'value' => 'UC123',
            'canonical_url' => 'https://www.youtube.com/channel/UC123',
        ]);
    }

    public function test_it_includes_safe_youtube_provider_error_message(): void
    {
        Config::set('services.youtube.data_api_key', 'youtube-secret');
        Http::fake([
            'https://www.googleapis.com/youtube/v3/channels*' => Http::response([
                'error' => [
                    'status' => 'PERMISSION_DENIED',
                    'message' => 'Requests to this API are blocked.',
                ],
            ], 403),
        ]);

        try {
            app(YoutubeChannelDataProvider::class)->fetch([
                'type' => 'channel',
                'value' => 'UC123',
                'canonical_url' => 'https://www.youtube.com/channel/UC123',
            ]);
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('Requests to this API are blocked.', $exception->errors()['channel_url'][0]);
            $this->assertStringNotContainsString('youtube-secret', $exception->errors()['channel_url'][0]);

            return;
        }

        $this->fail('Expected a validation exception.');
    }
}
