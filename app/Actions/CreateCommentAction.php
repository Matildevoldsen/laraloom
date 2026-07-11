<?php

namespace App\Actions;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CreateCommentAction
{
    /** @param array{body: string, parent_id?: int|null} $attributes */
    public function execute(User $user, Post $post, array $attributes): Comment
    {
        return Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'parent_id' => $attributes['parent_id'] ?? null,
            'body' => trim($attributes['body']),
        ]);
    }
}
