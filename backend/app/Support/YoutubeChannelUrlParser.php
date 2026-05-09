<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class YoutubeChannelUrlParser
{
    /**
     * @return array{type: string, value: string, canonical_url: string}
     */
    public static function parse(string $url): array
    {
        $parts = parse_url(trim($url));

        if (! is_array($parts) || ! isset($parts['host'])) {
            self::throwInvalid();
        }

        $host = strtolower($parts['host']);

        if (! in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            self::throwInvalid();
        }

        $segments = array_values(array_filter(explode('/', $parts['path'] ?? ''), 'strlen'));
        $firstSegment = $segments[0] ?? '';

        if (str_starts_with($firstSegment, '@')) {
            $handle = self::cleanValue($firstSegment);

            return [
                'type' => 'handle',
                'value' => $handle,
                'canonical_url' => "https://www.youtube.com/{$handle}",
            ];
        }

        if (in_array($firstSegment, ['channel', 'c', 'user'], true) && isset($segments[1])) {
            $value = self::cleanValue($segments[1]);

            return [
                'type' => $firstSegment,
                'value' => $value,
                'canonical_url' => "https://www.youtube.com/{$firstSegment}/{$value}",
            ];
        }

        self::throwInvalid();
    }

    private static function cleanValue(string $value): string
    {
        $decoded = rawurldecode($value);

        if ($decoded === '' || preg_match('/\s/', $decoded) === 1) {
            self::throwInvalid();
        }

        return $decoded;
    }

    private static function throwInvalid(): never
    {
        throw ValidationException::withMessages([
            'channel_url' => '지원하는 YouTube 채널 URL을 입력해 주세요.',
        ]);
    }
}
