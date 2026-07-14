<?php

namespace App\Actions;

use App\Data\StoredPostAttachment;
use App\Data\StoredPostAttachments;
use App\Models\Post;

class PersistPostAttachmentsAction
{
    public function execute(Post $post, StoredPostAttachments $attachments): void
    {
        if ($attachments->isEmpty()) {
            return;
        }

        $post->attachments()->createMany(
            array_map(
                fn (StoredPostAttachment $attachment): array => $attachment->toAttributes(),
                $attachments->items,
            ),
        );
    }
}
