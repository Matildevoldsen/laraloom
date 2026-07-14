<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user('sanctum');

        return [
            'id' => $this->getKey(),
            'kind' => $this->kind->value,
            'status' => $this->status->value,
            'title' => $this->title,
            'body' => $this->body,
            'summary' => $this->summary,
            'why_it_matters' => $this->why_it_matters,
            'url' => $this->url,
            'source' => [
                'name' => $this->source_name,
                'author' => $this->source_author,
            ],
            'tags' => $this->tags ?? [],
            'hashtags' => $this->whenLoaded('hashtags', fn () => $this->hashtags->map(fn ($hashtag): array => [
                'id' => $hashtag->getKey(),
                'name' => $hashtag->name,
                'slug' => $hashtag->slug,
            ])->values()),
            'mentions' => $this->whenLoaded('mentions', fn () => $this->mentions->map(fn ($mention): array => [
                'id' => $mention->getKey(),
                'handle' => $mention->handle,
                'user' => UserResource::make($mention->mentionedUser),
            ])->values()),
            'attachments' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($attachment): array => [
                'id' => $attachment->getKey(),
                'type' => $attachment->media_type,
                'mime_type' => $attachment->mime_type,
                'url' => route('post-attachments.show', $attachment),
            ])->values()),
            'is_ai_curated' => $this->is_ai_curated,
            'published_at' => $this->published_at?->toIso8601String(),
            'author' => UserResource::make($this->whenLoaded('user')),
            'counts' => [
                'reactions' => $this->whenCounted('reactingUsers'),
                'bookmarks' => $this->whenCounted('bookmarkingUsers'),
                'comments' => $this->whenCounted('comments'),
                'reposts' => $this->whenCounted('repostingUsers'),
            ],
            'is_reacted' => (bool) ($this->getAttribute('is_reacted') ?? false),
            'is_bookmarked' => (bool) ($this->getAttribute('is_bookmarked') ?? false),
            'is_reposted' => (bool) ($this->getAttribute('is_reposted') ?? false),
            'permissions' => [
                'update' => $user instanceof User && $user->can('update', $this->resource),
                'delete' => $user instanceof User && $user->can('delete', $this->resource),
                'moderate' => $user instanceof User && $user->is_admin,
            ],
        ];
    }
}
