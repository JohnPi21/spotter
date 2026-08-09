<?php

namespace App;

use Illuminate\Support\Str;

class YoutubeUrlParser
{
    private const array YOUTUBE_HOSTS = [
        'youtube.com',
        'www.youtube.com',
        'm.youtube.com',
    ];

    public function parse(string $videoReference): ?string
    {
        if (! Str::isUrl($videoReference, ['http', 'https'])) {
            return strlen($videoReference) === 11 ? $videoReference : null;
        }

        $parsedUrl = parse_url($videoReference);
        $host = strtolower($parsedUrl['host'] ?? '');

        if (! in_array($host, self::YOUTUBE_HOSTS, true)) {
            return null;
        }

        $path = $parsedUrl['path'] ?? '';

        if ($path === '/watch') {
            parse_str($parsedUrl['query'] ?? '', $query);
            $videoId = $query['v'] ?? null;

            return is_string($videoId) ? $videoId : null;
        }

        if (preg_match('#\A/shorts/([^/]+)/?\z#', $path, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
