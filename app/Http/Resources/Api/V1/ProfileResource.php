<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class ProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...UserResource::make($this->resource)->resolve($request),
            'is_following' => (bool) ($this->getAttribute('is_following') ?? false),
            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'replies' => CommentResource::collection($this->whenLoaded('comments')),
            'liked_posts' => PostResource::collection($this->whenLoaded('reactedPosts')),
            'reposted_posts' => PostResource::collection($this->whenLoaded('repostedPosts')),
            'followers' => UserResource::collection($this->whenLoaded('followers')),
            'following' => UserResource::collection($this->whenLoaded('following')),
        ];
    }
}
