<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
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
            'slug' => $this->slug,
            'kind' => $this->kind->value,
            'name' => $this->name,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'url' => $this->url,
            'repository_url' => $this->repository_url,
            'laravel_cloud_url' => $this->laravel_cloud_url,
            'logo_url' => $this->logo_url,
            'screenshot_url' => $this->screenshot_url,
            'tags' => $this->tags ?? [],
            'is_open_source' => $this->is_open_source,
            'is_featured' => $this->featured_at !== null,
            'published_at' => $this->published_at?->toIso8601String(),
            'author' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
