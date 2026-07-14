<?php

namespace App\Data;

final readonly class ExtractedPostReferences
{
    /**
     * @param  list<string>  $mentions
     * @param  array<string, string>  $hashtags
     */
    public function __construct(
        public array $mentions,
        public array $hashtags,
    ) {}
}
