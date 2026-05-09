<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnalysisPreviewTest extends TestCase
{
    public function test_analysis_requires_a_channel_url(): void
    {
        $this->postJson('/api/analysis', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('channel_url');
    }

    public function test_analysis_returns_a_real_korean_session_only_channel_insight(): void
    {
        Config::set('services.youtube.data_api_key', 'youtube-secret');
        Config::set('services.openai.api_key', 'openai-secret');
        Config::set('services.openai.model', 'gpt-5.2');

        Http::fake([
            'https://www.googleapis.com/youtube/v3/channels*' => Http::response([
                'items' => [
                    [
                        'id' => 'UC123',
                        'snippet' => [
                            'title' => 'Example Channel',
                            'description' => 'A channel about examples.',
                            'publishedAt' => '2023-01-01T00:00:00Z',
                            'customUrl' => '@example',
                            'thumbnails' => [],
                        ],
                        'statistics' => [
                            'subscriberCount' => '1000',
                            'videoCount' => '12',
                            'viewCount' => '34000',
                        ],
                    ],
                ],
            ]),
            'https://api.openai.com/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'summary' => 'Example Channel은 초보자에게 명확한 주제를 전달하는 채널입니다.',
                    'observations' => ['주제가 분명합니다.', '영상 수가 적당합니다.', '조회수 대비 구독 전환을 점검할 수 있습니다.'],
                    'next_actions' => ['대표 영상 3개를 비교하세요.', '업로드 리듬을 확인하세요.', '채널 소개를 다듬으세요.'],
                ], JSON_UNESCAPED_UNICODE),
            ]),
        ]);

        $this->postJson('/api/analysis', [
            'channel_url' => 'https://www.youtube.com/@example',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'ready')
            ->assertJsonPath('data.language', 'ko')
            ->assertJsonPath('data.storage', 'session_only')
            ->assertJsonPath('data.channel.type', 'handle')
            ->assertJsonPath('data.channel.value', '@example')
            ->assertJsonPath('data.channel.id', 'UC123')
            ->assertJsonPath('data.channel.title', 'Example Channel')
            ->assertJsonPath('data.channel.statistics.subscriber_count', 1000)
            ->assertJsonPath('data.source.youtube.provider', 'youtube_data_api')
            ->assertJsonPath('data.source.openai.provider', 'openai_responses')
            ->assertJsonCount(3, 'data.observations')
            ->assertJsonCount(3, 'data.next_actions')
            ->assertJsonMissing(['youtube-secret'])
            ->assertJsonMissing(['openai-secret']);
    }

    public function test_analysis_rejects_non_youtube_urls(): void
    {
        $this->postJson('/api/analysis', [
            'channel_url' => 'https://example.com/@example',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('channel_url');
    }
}
