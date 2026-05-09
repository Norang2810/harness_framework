<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class OpenAiChannelInsightProvider
{
    /**
     * @param  array<string, mixed>  $channel
     * @return array{summary: string, observations: array<int, string>, next_actions: array<int, string>}
     */
    public function analyze(array $channel): array
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.model', 'gpt-5.2');

        if (! is_string($apiKey) || $apiKey === '') {
            $this->fail('OPENAI_API_KEY가 설정되어 있지 않습니다.');
        }

        try {
            $response = Http::withToken($apiKey)
                ->withOptions(['verify' => config('services.http.verify_ssl', true)])
                ->timeout(20)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'instructions' => $this->instructions(),
                    'input' => $this->input($channel),
                ]);
        } catch (ConnectionException) {
            $this->fail('OpenAI Responses API에 연결할 수 없습니다.');
        }

        if (! $response->successful()) {
            $this->fail('OpenAI Responses API 요청에 실패했습니다.');
        }

        return $this->parseAnalysis($response->json());
    }

    private function instructions(): string
    {
        return implode("\n", [
            'You are a YouTube channel strategist.',
            'Respond only in Korean.',
            'Return JSON only with keys: summary, observations, next_actions.',
            'observations and next_actions must each contain exactly 3 concise strings.',
            'Do not mention unavailable transcript, OAuth, competitor, schedule, email, or database features as if they exist.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $channel
     */
    private function input(array $channel): string
    {
        return '다음 YouTube 채널 공개 메타데이터를 바탕으로 MVP용 한국어 분석을 작성하세요: '
            .json_encode($channel, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array{summary: string, observations: array<int, string>, next_actions: array<int, string>}
     */
    private function parseAnalysis(?array $payload): array
    {
        $text = $payload['output_text'] ?? $this->extractOutputText($payload ?? []);

        if (! is_string($text) || trim($text) === '') {
            $this->fail('OpenAI 응답에서 분석 텍스트를 찾지 못했습니다.');
        }

        $decoded = json_decode($text, true);

        if (! is_array($decoded)
            || ! is_string($decoded['summary'] ?? null)
            || ! is_array($decoded['observations'] ?? null)
            || ! is_array($decoded['next_actions'] ?? null)) {
            $this->fail('OpenAI 분석 응답 형식이 올바르지 않습니다.');
        }

        return [
            'summary' => $decoded['summary'],
            'observations' => array_values(array_map('strval', $decoded['observations'])),
            'next_actions' => array_values(array_map('strval', $decoded['next_actions'])),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractOutputText(array $payload): ?string
    {
        foreach (($payload['output'] ?? []) as $output) {
            if (! is_array($output)) {
                continue;
            }

            foreach (($output['content'] ?? []) as $content) {
                if (is_array($content) && ($content['type'] ?? null) === 'output_text' && is_string($content['text'] ?? null)) {
                    return $content['text'];
                }
            }
        }

        return null;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'channel_url' => $message,
        ]);
    }
}
