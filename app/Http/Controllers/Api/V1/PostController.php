<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreatePostAction;
use App\Actions\UpdatePostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function update(
        UpdatePostRequest $request,
        Post $post,
        UpdatePostAction $updatePost,
    ): PostResource {
        $post = $updatePost->execute($post, $request->validated());

        return PostResource::make(
            $post->load('user')->loadCount(['reactingUsers', 'bookmarkingUsers']),
        );
    }

    public function destroy(Post $post): JsonResponse
    {
        Gate::authorize('delete', $post);
        $post->delete();

        return response()->json(status: 204);
    }

    /**
     * Display a listing of the resource.
     */
    public function show(Request $request, Post $post): PostResource
    {
        $user = $request->user('sanctum');
        abort_unless(
            $post->published_at?->isPast() || ($user instanceof User && $user->can('update', $post)),
            404,
        );

        return PostResource::make(
            $post->load('user')->loadCount(['reactingUsers', 'bookmarkingUsers']),
        );
    }
}
