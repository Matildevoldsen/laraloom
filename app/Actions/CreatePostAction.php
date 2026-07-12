<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\User;
use App\PostStatus;
use App\Services\PostInputNormalizer;
use Illuminate\Support\Str;

class CreatePostAction
{
    public function __construct(
        private readonly PostInputNormalizer $normalizer,
        private readonly StorePostAttachmentsAction $storeAttachments,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $user, array $attributes): Post
    {
        $files = $attributes['attachments'] ?? [];
        unset($attributes['attachments']);
        $input = $this->normalizer->normalize($attributes);
        $title = $input['title'];

        $post = Post::create([
            'user_id' => $user->id,
            ...$input,
            'status' => PostStatus::Published,
            'slug' => $title ? Str::slug($title).'-'.Str::lower(Str::random(6)) : null,
            'is_ai_curated' => false,
            'published_at' => now(),
        ]);

        $this->storeAttachments->execute($post, $files);

        return $post;
    }
}
