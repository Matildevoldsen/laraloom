<?php

use App\Broadcasting\UserMessagesChannel;
use App\Broadcasting\UserNotificationsChannel;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    'sourcefolk.admin',
    fn (User $user): bool => $user->is_admin,
);

Broadcast::channel(
    'sourcefolk.users.{user}.messages',
    UserMessagesChannel::class,
    ['guards' => ['web', 'sanctum']],
);

Broadcast::channel(
    'sourcefolk.users.{user}.notifications',
    UserNotificationsChannel::class,
    ['guards' => ['web', 'sanctum']],
);
