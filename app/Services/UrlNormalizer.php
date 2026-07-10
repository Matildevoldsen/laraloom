<?php

namespace App\Services;

use InvalidArgumentException;

class UrlNormalizer
{
    public function normalize(string $url): string
    {
        $url = trim($url);
        $parts = parse_url($url);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException('A valid absolute URL is required.');
        }

        $scheme = strtolower((string) $parts['scheme']);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Only HTTP and HTTPS URLs are supported.');
        }

        $host = strtolower((string) $parts['host']);
        $path = $parts['path'] ?? '/';
        $query = $this->normalizeQuery($parts['query'] ?? null);
        $port = isset($parts['port']) && ! in_array($parts['port'], [80, 443], true)
            ? ':'.$parts['port']
            : '';

        return $scheme.'://'.$host.$port.$path.($query === '' ? '' : '?'.$query);
    }

    private function normalizeQuery(mixed $query): string
    {
        if (! is_string($query) || $query === '') {
            return '';
        }

        parse_str($query, $parameters);

        foreach (array_keys($parameters) as $key) {
            if (str_starts_with(strtolower((string) $key), 'utm_') || in_array($key, ['fbclid', 'gclid'], true)) {
                unset($parameters[$key]);
            }
        }

        ksort($parameters);

        return http_build_query($parameters);
    }
}
