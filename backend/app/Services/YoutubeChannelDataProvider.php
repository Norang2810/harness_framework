<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class YoutubeChannelDataProvider
{
    /**
     * @param  array{type: string, value: string, canonical_url: string}  $channelReference
     * @return array<string, mixed>
     */
    public function fetch(array $channelReference): array
    {
        $apiKey = config('services.youtube.data_api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            $this->fail('YOUTUBE_DATA_API_KEY가 설정되어 있지 않습니다.');
        }

        $lookup = $this->lookupParameters($channelReference);
        try {
            $response = Http::withOptions(['verify' => config('services.http.verify_ssl', true)])
                ->timeout(10)
                ->get('https://www.googleapis.com/youtube/v3/channels', [
                    'part' => 'snippet,statistics',
                    'key' => $apiKey,
                    ...$lookup['params'],
                ]);
        } catch (ConnectionException) {
            $this->fail('YouTube Data API에 연결할 수 없습니다.');
        }

        if (! $response->successful()) {
            $message = $response->json('error.message');
            $this->fail('YouTube Data API 요청에 실패했습니다.'
                .(is_string($message) && $message !== '' ? " {$message}" : ''));
        }

        $item = $response->json('items.0');

        if (! is_array($item)) {
            $this->fail('입력한 URL에 해당하는 YouTube 채널을 찾지 못했습니다.');
        }

        return $this->normalizeChannel($item, $lookup['method']);
    }

    /**
     * @param  array{type: string, value: string, canonical_url: string}  $channelReference
     * @return array{method: string, params: array<string, string>}
     */
    private function lookupParameters(array $channelReference): array
    {
        return match ($channelReference['type']) {
            'channel' => ['method' => 'id', 'params' => ['id' => $channelReference['value']]],
            'user' => ['method' => 'forUsername', 'params' => ['forUsername' => $channelReference['value']]],
            'handle', 'c' => ['method' => 'forHandle', 'params' => ['forHandle' => $channelReference['value']]],
            default => $this->fail('지원하는 YouTube 채널 URL을 입력해 주세요.'),
        };
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizeChannel(array $item, string $lookupMethod): array
    {
        $id = (string) ($item['id'] ?? '');
        $snippet = is_array($item['snippet'] ?? null) ? $item['snippet'] : [];
        $statistics = is_array($item['statistics'] ?? null) ? $item['statistics'] : [];

        if ($id === '') {
            $this->fail('YouTube Data API 응답에 채널 ID가 없습니다.');
        }

        return [
            'id' => $id,
            'title' => (string) ($snippet['title'] ?? ''),
            'description' => (string) ($snippet['description'] ?? ''),
            'published_at' => $snippet['publishedAt'] ?? null,
            'custom_url' => $snippet['customUrl'] ?? null,
            'country' => $snippet['country'] ?? null,
            'thumbnails' => $snippet['thumbnails'] ?? [],
            'statistics' => [
                'subscriber_count' => $this->intOrNull($statistics['subscriberCount'] ?? null),
                'video_count' => $this->intOrNull($statistics['videoCount'] ?? null),
                'view_count' => $this->intOrNull($statistics['viewCount'] ?? null),
                'hidden_subscriber_count' => (bool) ($statistics['hiddenSubscriberCount'] ?? false),
            ],
            'canonical_url' => "https://www.youtube.com/channel/{$id}",
            'source' => [
                'provider' => 'youtube_data_api',
                'endpoint' => 'channels.list',
                'lookup' => $lookupMethod,
            ],
        ];
    }

    private function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'channel_url' => $message,
        ]);
    }
}
