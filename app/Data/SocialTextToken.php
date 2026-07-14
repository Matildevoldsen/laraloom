<?php

namespace App\Data;

use App\SocialTextTokenType;

final readonly class SocialTextToken
{
    public function __construct(
        public SocialTextTokenType $type,
        public string $text,
        public ?string $value = null,
    ) {}
}
