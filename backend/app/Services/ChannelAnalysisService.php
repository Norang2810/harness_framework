<?php

namespace App\Services;

use App\Support\YoutubeChannelUrlParser;

class ChannelAnalysisService
{
    public function __construct(
        private readonly YoutubeChannelDataProvider $youtube,
        private readonly OpenAiChannelInsightProvider $openAi,
    ) {}

    /**
     * @return array{
     *     status: string,
     *     language: string,
     *     storage: string,
     *     channel: array<string, mixed>,
     *     source: array<string, mixed>,
     *     summary: string,
     *     observations: array<int, string>,
     *     next_actions: array<int, string>
     * }
     */
    public function analyze(string $channelUrl): array
    {
        $channelReference = YoutubeChannelUrlParser::parse($channelUrl);
        $youtubeChannel = $this->youtube->fetch($channelReference);
        $analysis = $this->openAi->analyze($youtubeChannel);

        return [
            'status' => 'ready',
            'language' => 'ko',
            'storage' => 'session_only',
            'channel' => [
                ...$channelReference,
                ...$youtubeChannel,
            ],
            'source' => [
                'youtube' => $youtubeChannel['source'],
                'openai' => [
                    'provider' => 'openai_responses',
                    'model' => config('services.openai.model', 'gpt-5.2'),
                ],
            ],
            'summary' => $analysis['summary'],
            'observations' => $analysis['observations'],
            'next_actions' => $analysis['next_actions'],
        ];
    }
}
