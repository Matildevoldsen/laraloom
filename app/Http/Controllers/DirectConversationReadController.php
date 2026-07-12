<?php

namespace App\Http\Controllers;

use App\Actions\MarkDirectConversationReadAction;
use App\Models\DirectConversation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DirectConversationReadController extends Controller
{
    public function __invoke(
        Request $request,
        DirectConversation $conversation,
        MarkDirectConversationReadAction $markRead,
    ): RedirectResponse|Response {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $markRead->execute($user, $conversation);

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return back();
    }
}
