<?php

namespace Tests\Feature;

use App\Services\OpenAiChannelInsightProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OpenAiChannelInsightProviderTest extends TestCase
{
    public function test_it_requests_korean_structured_analysis_from_openai(): void
    {
        Config::set('services.openai.api_key', 'openai-secret');
        Config::set('services.openai.model', 'gpt-5.2');
        $requestWasAsserted = false;
        Http::fake([
            'https://api.openai.com/v1/responses' => function ($request) use (&$requestWasAsserted) {
                $requestWasAsserted = true;
                $body = $request->data();

                $this->assertSame('POST', $request->method());
                $this->assertTrue($request->hasHeader('Authorization', 'Bearer openai-secret'));
                $this->assertSame('gpt-5.2', $body['model']);
                $this->assertStringContainsString('Korean', $body['instructions']);
                $this->assertStringContainsString('한국어', $body['input']);
                $this->assertStringContainsString('Acme Studio', $body['input']);

                return Http::response([
                    'output' => [
                        [
                            'type' => 'message',
                            'content' => [
                                [
                                    'type' => 'output_text',
                                    'text' => json_encode([
                                        'summary' => '한국어 요약입니다.',
                                        'observations' => ['관찰 1', '관찰 2', '관찰 3'],
                                        'next_actions' => ['액션 1', '액션 2', '액션 3'],
                                    ], JSON_UNESCAPED_UNICODE),
                                ],
                            ],
                        ],
                    ],
                ]);
            },
        ]);

        $analysis = app(OpenAiChannelInsightProvider::class)->analyze([
            'id' => 'UC123',
            'title' => 'Acme Studio',
            'description' => 'Channel description',
            'statistics' => ['subscriber_count' => 1200, 'video_count' => 42, 'view_count' => 99000],
        ]);

        $this->assertSame('한국어 요약입니다.', $analysis['summary']);
        $this->assertCount(3, $analysis['observations']);
        $this->assertCount(3, $analysis['next_actions']);
        $this->assertTrue($requestWasAsserted);
    }

    public function test_it_reports_malformed_openai_output_as_validation_error(): void
    {
        Config::set('services.openai.api_key', 'openai-secret');
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response(['output_text' => 'not json']),
        ]);

        $this->expectException(ValidationException::class);

        app(OpenAiChannelInsightProvider::class)->analyze([
            'id' => 'UC123',
            'title' => 'Acme Studio',
            'description' => '',
            'statistics' => [],
        ]);
    }

    public function test_it_reports_openai_connection_failure_as_validation_error(): void
    {
        Config::set('services.openai.api_key', 'openai-secret');
        Http::fake([
            'https://api.openai.com/v1/responses' => fn () => throw new ConnectionException('network down'),
        ]);

        $this->expectException(ValidationException::class);

        app(OpenAiChannelInsightProvider::class)->analyze([
            'id' => 'UC123',
            'title' => 'Acme Studio',
            'description' => '',
            'statistics' => [],
        ]);
    }
}
