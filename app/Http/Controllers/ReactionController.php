<?php

namespace App\Http\Controllers;

use App\Actions\ToggleReactionAction;
use App\Models\Post;
use App\Models\User;
use App\PostStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function __invoke(
        Request $request,
        Post $post,
        ToggleReactionAction $toggleReaction,
    ): JsonResponse|RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        abort_unless($post->status === PostStatus::Published && $post->published_at?->isPast(), 404);
        $active = $toggleReaction->execute($user, $post);

        if ($request->expectsJson()) {
            return response()->json([
                'active' => $active,
                'count' => $post->reactingUsers()->count(),
            ]);
        }

        return back();
    }
}
