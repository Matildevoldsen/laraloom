<?php

namespace App;

enum CommunityNotificationType: string
{
    case Comment = 'comment';
    case Follow = 'follow';
    case Reaction = 'reaction';
    case Reply = 'reply';
    case Repost = 'repost';

    public function verb(): string
    {
        return match ($this) {
            self::Comment => 'commented on your post',
            self::Follow => 'followed you',
            self::Reaction => 'liked your post',
            self::Reply => 'replied to your comment',
            self::Repost => 'reposted your post',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Comment, self::Reply => 'chat-bubble-left',
            self::Follow => 'user-plus',
            self::Reaction => 'heart',
            self::Repost => 'arrow-path-rounded-square',
        };
    }
}
