<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ToggleReactionAction;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function __invoke(Request $request, Post $post, ToggleReactionAction $toggleReaction): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        abort_unless($post->published_at?->isPast(), 404);

        $isReacted = $toggleReaction->execute($user, $post);

        return response()->json([
            'active' => $isReacted,
            'count' => $post->reactingUsers()->count(),
        ]);
    }
}
