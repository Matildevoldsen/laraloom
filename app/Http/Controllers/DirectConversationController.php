<?php

namespace App\Http\Controllers;

use App\Actions\StartDirectConversationAction;
use App\Http\Requests\StartDirectConversationRequest;
use App\Models\DirectConversation;
use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DirectConversationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->authenticatedUser($request);

        return $this->inboxView($user);
    }

    public function show(Request $request, DirectConversation $conversation): View
    {
        $user = $this->authenticatedUser($request);
        Gate::forUser($user)->authorize('view', $conversation);

        $conversation->load([
            'participantOne',
            'participantTwo',
            'latestMessage',
            'states' => fn ($query) => $query->where('user_id', $user->id),
        ]);
        $messages = $conversation->messages()
            ->with('sender')
            ->latest('id')
            ->limit(80)
            ->get()
            ->reverse()
            ->values();

        return $this->inboxView($user, $conversation, $messages);
    }

    public function store(
        StartDirectConversationRequest $request,
        User $recipient,
        StartDirectConversationAction $startConversation,
    ): RedirectResponse {
        $sender = $this->authenticatedUser($request);
        $conversation = $startConversation->execute($sender, $recipient);

        return to_route('direct-messages.show', $conversation);
    }

    /**
     * @param  Collection<int, DirectMessage>|null  $messages
     */
    private function inboxView(
        User $user,
        ?DirectConversation $selectedConversation = null,
        ?Collection $messages = null,
    ): View {
        $conversations = DirectConversation::query()
            ->forUser($user)
            ->with([
                'participantOne',
                'participantTwo',
                'latestMessage.sender',
                'states' => fn ($query) => $query->where('user_id', $user->id),
            ])
            ->latest('last_message_at')
            ->latest('id')
            ->limit(50)
            ->get();
        $recipients = $user->followers()
            ->whereNotNull('username')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return view('direct-messages.index', [
            'conversations' => $conversations,
            'messages' => $messages ?? new Collection,
            'recipients' => $recipients,
            'selectedConversation' => $selectedConversation,
            'viewer' => $user,
        ]);
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
