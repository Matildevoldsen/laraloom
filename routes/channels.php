<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    'laraloom.admin',
    fn (User $user): bool => $user->is_admin,
);
