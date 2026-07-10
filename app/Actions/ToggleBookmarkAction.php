<?php

namespace App\Actions;

use App\Models\Bookmark;
use App\Models\Post;
use App\Models\User;

class ToggleBookmarkAction
{
    public function execute(User $user, Post $post): bool
    {
        $bookmark = Bookmark::query()
            ->whereBelongsTo($user)
            ->whereBelongsTo($post)
            ->first();

        if ($bookmark) {
            $bookmark->delete();

            return false;
        }

        Bookmark::create(['user_id' => $user->id, 'post_id' => $post->id]);

        return true;
    }
}
