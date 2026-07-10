<?php

namespace App\Actions;

use App\Models\Follow;
use App\Models\User;
use InvalidArgumentException;

class ToggleFollowAction
{
    public function execute(User $follower, User $following): bool
    {
        if ($follower->is($following)) {
            throw new InvalidArgumentException('You cannot follow yourself.');
        }

        $follow = Follow::query()
            ->where('follower_id', $follower->id)
            ->where('following_id', $following->id)
            ->first();

        if ($follow) {
            $follow->delete();

            return false;
        }

        Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $following->id,
        ]);

        return true;
    }
}
