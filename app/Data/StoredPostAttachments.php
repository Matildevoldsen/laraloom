<?php

namespace App\Data;

final readonly class StoredPostAttachments
{
    /**
     * @param  list<StoredPostAttachment>  $items
     */
    public function __construct(public array $items) {}

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /** @return array<string, list<string>> */
    public function pathsByDisk(): array
    {
        $pathsByDisk = [];

        foreach ($this->items as $attachment) {
            $pathsByDisk[$attachment->disk][] = $attachment->path;
        }

        return $pathsByDisk;
    }
}
