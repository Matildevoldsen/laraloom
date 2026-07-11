<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateCommentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\Api\V1\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function index(Post $post): AnonymousResourceCollection
    {
        abort_unless($post->published_at?->isPast(), 404);

        return CommentResource::collection(
            $post->comments()
                ->with('user')
                ->withCount('replies')
                ->oldest()
                ->get(),
        );
    }

    public function store(
        StoreCommentRequest $request,
        Post $post,
        CreateCommentAction $createComment,
    ): JsonResponse {
        Gate::authorize('create', Comment::class);
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $comment = $createComment->execute($user, $post, $request->commentData());

        return CommentResource::make(
            $comment->load('user')->loadCount('replies'),
        )->response()->setStatusCode(201);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);
        $comment->delete();

        return response()->json(status: 204);
    }
}
