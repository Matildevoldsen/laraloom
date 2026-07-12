<?php

namespace App\Policies;

use App\Models\DirectConversation;
use App\Models\Follow;
use App\Models\User;

class DirectConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DirectConversation $conversation): bool
    {
        return $conversation->hasParticipant($user);
    }

    public function create(User $sender, User $recipient): bool
    {
        if ($sender->is($recipient)) {
            return false;
        }

        return Follow::query()
            ->where('follower_id', $recipient->id)
            ->where('following_id', $sender->id)
            ->exists();
    }

    public function send(User $user, DirectConversation $conversation): bool
    {
        return $conversation->hasParticipant($user)
            && $conversation->hasActiveFollow();
    }

    public function markRead(User $user, DirectConversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
