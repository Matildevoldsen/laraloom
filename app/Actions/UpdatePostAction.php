<?php

namespace App\Actions;

use App\Models\Post;
use App\Services\PostInputNormalizer;
use Illuminate\Support\Str;

class UpdatePostAction
{
    public function __construct(private readonly PostInputNormalizer $normalizer) {}

    /** @param array<string, mixed> $attributes */
    public function execute(Post $post, array $attributes): Post
    {
        $input = $this->normalizer->normalize($attributes);
        $title = $input['title'];

        $post->update([
            ...$input,
            'slug' => $title === null
                ? null
                : Str::slug($title).'-'.Str::lower(Str::random(6)),
        ]);

        return $post->refresh();
    }
}
