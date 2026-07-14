<?php

namespace App\Data;

use App\PostAttachmentMediaType;

final readonly class StoredPostAttachment
{
    public function __construct(
        public string $disk,
        public string $path,
        public PostAttachmentMediaType $mediaType,
        public string $mimeType,
        public string $originalName,
        public int $size,
    ) {}

    /**
     * @return array{
     *     disk: string,
     *     path: string,
     *     media_type: string,
     *     mime_type: string,
     *     original_name: string,
     *     size: int
     * }
     */
    public function toAttributes(): array
    {
        return [
            'disk' => $this->disk,
            'path' => $this->path,
            'media_type' => $this->mediaType->value,
            'mime_type' => $this->mimeType,
            'original_name' => $this->originalName,
            'size' => $this->size,
        ];
    }
}
