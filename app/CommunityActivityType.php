<?php

namespace App;

enum CommunityActivityType: string
{
    case CommentCreated = 'comment.created';
    case CommentDeleted = 'comment.deleted';
    case PostCreated = 'post.created';
    case PostDeleted = 'post.deleted';
    case PostUpdated = 'post.updated';
    case ReactionCreated = 'reaction.created';
    case ReactionDeleted = 'reaction.deleted';
    case RepostCreated = 'repost.created';
    case RepostDeleted = 'repost.deleted';
}
