<?php

namespace App\Actions;

use App\Models\Post;
use App\Services\PostInputNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdatePostAction
{
    public function __construct(
        private readonly PostInputNormalizer $normalizer,
        private readonly SyncPostReferencesAction $syncReferences,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function execute(Post $post, array $attributes): Post
    {
        $input = $this->normalizer->normalize($attributes);
        $title = $input['title'];

        return DB::transaction(function () use ($input, $post, $title): Post {
            $lockedPost = Post::query()
                ->whereKey($post->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedPost->update([
                ...$input,
                'slug' => $title === null
                    ? null
                    : Str::slug($title).'-'.Str::lower(Str::random(6)),
            ]);

            $this->syncReferences->execute($lockedPost);

            return $lockedPost->refresh();
        }, attempts: 3);
    }
}
