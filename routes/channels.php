<?php

use App\Broadcasting\UserMessagesChannel;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    'laraloom.admin',
    fn (User $user): bool => $user->is_admin,
);

Broadcast::channel(
    'laraloom.users.{user}.messages',
    UserMessagesChannel::class,
    ['guards' => ['web', 'sanctum']],
);
