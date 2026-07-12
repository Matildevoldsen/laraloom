<?php

namespace App\Http\Controllers;

use App\Actions\SendDirectMessageAction;
use App\Http\Requests\StoreDirectMessageRequest;
use App\Models\DirectConversation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class DirectMessageController extends Controller
{
    public function __invoke(
        StoreDirectMessageRequest $request,
        DirectConversation $conversation,
        SendDirectMessageAction $sendMessage,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $data = $request->messageData();
        $sendMessage->execute(
            $user,
            $conversation,
            $data['body'],
            $data['client_id'],
        );

        return to_route('direct-messages.show', $conversation);
    }
}
