<?php

namespace App\Broadcasting;

use App\Models\User;

class UserMessagesChannel
{
    public function join(User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser->is($user);
    }
}
