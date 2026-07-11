<?php

namespace App\Actions;

use App\Models\Post;
use App\PostStatus;

class ModeratePostAction
{
    public function execute(Post $post, PostStatus $status): Post
    {
        $post->update([
            'status' => $status,
            'published_at' => $status === PostStatus::Published
                ? ($post->published_at ?? now())
                : null,
        ]);

        return $post->refresh();
    }
}
