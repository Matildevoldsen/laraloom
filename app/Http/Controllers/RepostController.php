<?php

namespace App\Http\Controllers;

use App\Actions\ToggleRepostAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RepostController extends Controller
{
    public function __invoke(Request $request, Post $post, ToggleRepostAction $toggleRepost): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $toggleRepost->execute($user, $post);

        return back();
    }
}
