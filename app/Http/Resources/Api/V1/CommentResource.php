<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Comment */
class CommentResource extends JsonResource
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
            'post_id' => $this->post_id,
            'parent_id' => $this->parent_id,
            'body' => $this->body,
            'created_at' => $this->created_at?->toIso8601String(),
            'author' => UserResource::make($this->whenLoaded('user')),
            'replies_count' => $this->whenCounted('replies'),
            'permissions' => [
                'delete' => $user instanceof User && $user->can('delete', $this->resource),
            ],
        ];
    }
}
