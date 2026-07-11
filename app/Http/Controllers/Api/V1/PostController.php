<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreatePostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function store(StorePostRequest $request, CreatePostAction $createPost): JsonResponse
    {
        Gate::authorize('create', Post::class);
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $post = $createPost->execute($user, $request->validated());

        return PostResource::make(
            $post->load('user')->loadCount(['reactingUsers', 'bookmarkingUsers']),
        )->response()->setStatusCode(201);
    }

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
