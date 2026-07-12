<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'username' => $this->username,
            'headline' => $this->headline,
            'bio' => $this->bio,
            'location' => $this->location,
            'website_url' => $this->website_url,
            'github_username' => $this->github_username,
            'x_username' => $this->x_username,
            'avatar_url' => $this->avatarUrl(),
            'stack' => $this->stack ?? [],
            'is_available_for_work' => $this->is_available_for_work,
            'is_admin' => $this->when($request->user()?->is($this->resource), $this->is_admin),
            'counts' => [
                'followers' => $this->whenCounted('followers'),
                'following' => $this->whenCounted('following'),
                'posts' => $this->whenCounted('posts'),
                'projects' => $this->whenCounted('projects'),
            ],
        ];
    }
}
