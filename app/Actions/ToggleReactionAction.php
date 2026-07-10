<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;

class ToggleReactionAction
{
    public function execute(User $user, Post $post): bool
    {
        $reaction = Reaction::query()
            ->whereBelongsTo($user)
            ->whereBelongsTo($post)
            ->first();

        if ($reaction) {
            $reaction->delete();

            return false;
        }

        Reaction::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'kind' => 'spark',
        ]);

        return true;
    }
}
