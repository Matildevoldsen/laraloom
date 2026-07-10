<?php

namespace App\Http\Controllers;

use App\Actions\ToggleReactionAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function __invoke(Request $request, Post $post, ToggleReactionAction $toggleReaction): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $toggleReaction->execute($user, $post);

        return back();
    }
}
