<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteUserAction
{
    public function execute(User $user): void
    {
        $avatarDisk = $user->avatar_disk;
        $avatarPath = $user->avatar_path;

        DB::transaction(function () use ($user): void {
            $this->deleteAgentConversations($user);
            $user->tokens()->delete();

            DB::table(config()->string('session.table'))
                ->where('user_id', $user->getKey())
                ->delete();

            DB::table(config()->string('auth.passwords.users.table', 'password_reset_tokens'))
                ->where('email', $user->email)
                ->delete();

            $user->delete();
        });

        if (is_string($avatarDisk) && is_string($avatarPath) && $avatarDisk !== '' && $avatarPath !== '') {
            Storage::disk($avatarDisk)->delete($avatarPath);
        }
    }

    private function deleteAgentConversations(User $user): void
    {
        $conversationsTable = config()->string('ai.conversations.tables.conversations', 'agent_conversations');
        $messagesTable = config()->string('ai.conversations.tables.messages', 'agent_conversation_messages');
        $conversationIds = DB::table($conversationsTable)
            ->where('user_id', $user->getKey())
            ->pluck('id');

        DB::table($messagesTable)
            ->where(function ($query) use ($conversationIds, $user): void {
                $query->where('user_id', $user->getKey());

                if ($conversationIds->isNotEmpty()) {
                    $query->orWhereIn('conversation_id', $conversationIds);
                }
            })
            ->delete();

        DB::table($conversationsTable)
            ->whereIn('id', $conversationIds)
            ->delete();
    }
}
