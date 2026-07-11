<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\Repost;
use App\Models\User;

class ToggleRepostAction
{
    public function execute(User $user, Post $post): bool
    {
        $repost = Repost::query()
            ->whereBelongsTo($user)
            ->whereBelongsTo($post)
            ->first();

        if ($repost) {
            $repost->delete();

            return false;
        }

        Repost::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        return true;
    }
}
