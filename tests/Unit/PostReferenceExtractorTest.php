<?php

use App\Services\PostReferenceExtractor;
use App\SocialTextTokenType;

test('it extracts normalized mentions and unicode hashtags from social text', function (): void {
    $references = app(PostReferenceExtractor::class)->extract(
        'Hello @Taylor-dev and @maker_one. #Laravel #laravel #Läravel_13',
    );

    expect($references->mentions)->toBe(['taylor-dev', 'maker_one'])
        ->and($references->hashtags)->toBe([
            'laravel' => 'Laravel',
            'läravel_13' => 'Läravel_13',
        ]);
});

test('it ignores embedded handles and references inside urls', function (): void {
    $extractor = app(PostReferenceExtractor::class);
    $references = $extractor->extract(
        'mail@example.com word@member https://sourcefolk.com/@person/#Laravel www.example.com/#Livewire @real_member #Real',
    );

    expect($references->mentions)->toBe(['real_member'])
        ->and($references->hashtags)->toBe(['real' => 'Real'])
        ->and(collect($extractor->tokens('https://sourcefolk.com/#Laravel @real_member'))
            ->where('type', SocialTextTokenType::Hashtag))->toBeEmpty()
        ->and(collect($extractor->tokens('https://sourcefolk.com/#Laravel @real_member'))
            ->where('type', SocialTextTokenType::Mention)->pluck('value')->all())->toBe(['real_member']);
});

test('it tokenizes references without changing the original text', function (): void {
    $text = "First #Laravel\nthen @maker-one and <script>alert(1)</script>";
    $tokens = app(PostReferenceExtractor::class)->tokens($text);

    expect(collect($tokens)->pluck('text')->implode(''))->toBe($text)
        ->and(collect($tokens)->where('type', SocialTextTokenType::Hashtag)->sole()->value)->toBe('laravel')
        ->and(collect($tokens)->where('type', SocialTextTokenType::Mention)->sole()->value)->toBe('maker-one');
});
