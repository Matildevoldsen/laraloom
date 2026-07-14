<?php

namespace App\Services;

use App\Data\ExtractedPostReferences;
use App\Data\SocialTextToken;
use App\SocialTextTokenType;
use Illuminate\Support\Str;

final class PostReferenceExtractor
{
    private const string HASHTAG_PATTERN = '/(?<![\pL\pN_])#([\pL\pN_]{1,100})/u';

    private const string MENTION_PATTERN = '/(?<![A-Za-z0-9_-])@([A-Za-z0-9_-]{1,30})/';

    private const string TOKEN_PATTERN = '/((?<![A-Za-z0-9_-])@[A-Za-z0-9_-]{1,30}|(?<![\pL\pN_])#[\pL\pN_]{1,100})/u';

    public function extract(?string $text): ExtractedPostReferences
    {
        $text ??= '';

        return new ExtractedPostReferences(
            mentions: $this->extractMentions($text),
            hashtags: $this->extractHashtags($text),
        );
    }

    /** @return list<SocialTextToken> */
    public function tokens(?string $text): array
    {
        if (blank($text)) {
            return [];
        }

        $parts = preg_split(
            self::TOKEN_PATTERN,
            $text,
            flags: PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE,
        );

        if ($parts === false) {
            return [new SocialTextToken(SocialTextTokenType::Text, $text)];
        }

        return array_map(function (array $part) use ($text): SocialTextToken {
            [$value, $offset] = $part;

            if ($this->isInsideUrl($text, $offset)) {
                return new SocialTextToken(SocialTextTokenType::Text, $value);
            }

            if (preg_match('/^@[A-Za-z0-9_-]{1,30}$/', $value) === 1) {
                return new SocialTextToken(
                    SocialTextTokenType::Mention,
                    $value,
                    Str::lower(Str::after($value, '@')),
                );
            }

            if (preg_match('/^#[\pL\pN_]{1,100}$/u', $value) === 1) {
                return new SocialTextToken(
                    SocialTextTokenType::Hashtag,
                    $value,
                    Str::lower(Str::after($value, '#')),
                );
            }

            return new SocialTextToken(SocialTextTokenType::Text, $value);
        }, $parts);
    }

    /** @return list<string> */
    private function extractMentions(string $text): array
    {
        $matched = preg_match_all(
            self::MENTION_PATTERN,
            $text,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
        );

        if ($matched === false || $matched === 0) {
            return [];
        }

        $handles = [];

        foreach ($matches as $match) {
            if (! $this->isInsideUrl($text, $match[0][1])) {
                $handles[] = Str::lower($match[1][0]);
            }
        }

        return array_values(array_unique($handles));
    }

    /** @return array<string, string> */
    private function extractHashtags(string $text): array
    {
        $matched = preg_match_all(
            self::HASHTAG_PATTERN,
            $text,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
        );

        if ($matched === false || $matched === 0) {
            return [];
        }

        $hashtags = [];

        foreach ($matches as $match) {
            if ($this->isInsideUrl($text, $match[0][1])) {
                continue;
            }

            $name = $match[1][0];
            $hashtags[Str::lower($name)] ??= $name;
        }

        return $hashtags;
    }

    private function isInsideUrl(string $text, int $offset): bool
    {
        $prefix = substr($text, 0, $offset);

        if (preg_match('/\S*$/u', $prefix, $matches) !== 1) {
            return false;
        }

        $segment = ltrim($matches[0], "([{<'\"");

        return preg_match('/^(?:https?:\/\/|www\.)/i', $segment) === 1;
    }
}
