<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function show(Post $post): PostResource
    {
        abort_unless($post->published_at?->isPast(), 404);

        return PostResource::make(
            $post->load('user')->loadCount(['reactingUsers', 'bookmarkingUsers']),
        );
    }
}
