<?php

namespace App\Broadcasting;

use App\Models\User;

class UserNotificationsChannel
{
    public function join(User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser->is($user);
    }
}
