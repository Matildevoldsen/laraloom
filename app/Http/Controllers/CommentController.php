<?php

namespace App\Http\Controllers;

use App\Actions\CreateCommentAction;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(
        StoreCommentRequest $request,
        Post $post,
        CreateCommentAction $createComment,
    ): RedirectResponse {
        Gate::authorize('create', Comment::class);
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $createComment->execute($user, $post, $request->commentData());

        return to_route('posts.show', $post)->with('status', 'Reply posted.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);
        $comment->delete();

        return back()->with('status', 'Reply deleted.');
    }
}
