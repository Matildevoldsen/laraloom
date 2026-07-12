<?php

use App\Actions\MarkDirectConversationReadAction;
use App\Actions\SendDirectMessageAction;
use App\Actions\StartDirectConversationAction;
use App\Broadcasting\UserMessagesChannel;
use App\Events\DirectMessageCreated;
use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

function directMessageConversation(): array
{
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $recipient->following()->attach($sender);
    $conversation = app(StartDirectConversationAction::class)->execute($sender, $recipient);

    return [$sender, $recipient, $conversation];
}

test('message bodies are encrypted at rest and decrypted only through the model', function () {
    [$sender, , $conversation] = directMessageConversation();
    $plaintext = 'A private Laravel release plan.';
    $message = app(SendDirectMessageAction::class)->execute($sender, $conversation, $plaintext);
    $storedBody = DB::table('direct_messages')->where('id', $message->id)->value('body');

    expect($storedBody)->toBeString()
        ->not->toBe($plaintext)
        ->not->toContain($plaintext)
        ->and($message->fresh()->body)->toBe($plaintext);
});

test('message broadcasts contain identifiers but never plaintext', function () {
    [$sender, $recipient, $conversation] = directMessageConversation();
    $event = new DirectMessageCreated(
        $conversation->id,
        91,
        $sender->id,
        $conversation->participantIds(),
        '2026-07-12T20:00:00+00:00',
    );
    $payload = $event->broadcastWith();
    $channels = collect($event->broadcastOn());

    expect($event)->toBeInstanceOf(ShouldDispatchAfterCommit::class)
        ->and($event->broadcastAs())->toBe('message.created')
        ->and($payload)->toBe([
            'conversation_id' => $conversation->id,
            'message_id' => 91,
            'sender_id' => $sender->id,
            'occurred_at' => '2026-07-12T20:00:00+00:00',
        ])
        ->and($payload)->not->toHaveKeys(['body', 'message'])
        ->and($channels)->each->toBeInstanceOf(PrivateChannel::class)
        ->and($channels->pluck('name')->sort()->values()->all())->toBe(collect([
            "private-sourcefolk.users.{$sender->id}.messages",
            "private-sourcefolk.users.{$recipient->id}.messages",
        ])->sort()->values()->all());
});

test('the private user message channel authorizes only its owner', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $channel = new UserMessagesChannel;

    expect($channel->join($owner, $owner))->toBeTrue()
        ->and($channel->join($outsider, $owner))->toBeFalse()
        ->and($channel->join($admin, $owner))->toBeFalse();
});

test('a client message identifier makes retries idempotent', function () {
    [$sender, , $conversation] = directMessageConversation();
    Event::fake([DirectMessageCreated::class]);
    $clientId = Str::uuid()->toString();
    $send = app(SendDirectMessageAction::class);

    $first = $send->execute($sender, $conversation, 'Only create this once.', $clientId);
    $retry = $send->execute($sender, $conversation, 'Only create this once.', $clientId);

    expect($retry->is($first))->toBeTrue();
    $this->assertDatabaseCount('direct_messages', 1);
    Event::assertDispatchedTimes(DirectMessageCreated::class, 1);
});

test('a client identifier cannot retrieve another users message', function () {
    [$firstSender, , $firstConversation] = directMessageConversation();
    [$secondSender, , $secondConversation] = directMessageConversation();
    $clientId = Str::uuid()->toString();
    $send = app(SendDirectMessageAction::class);
    $send->execute($firstSender, $firstConversation, 'First private message.', $clientId);

    $this->expectException(ValidationException::class);
    $send->execute($secondSender, $secondConversation, 'Collision attempt.', $clientId);
});

test('read positions belong to the conversation and never move backwards', function () {
    [$sender, $recipient, $conversation] = directMessageConversation();
    $send = app(SendDirectMessageAction::class);
    $first = $send->execute($sender, $conversation, 'First');
    $second = $send->execute($sender, $conversation, 'Second');
    $markRead = app(MarkDirectConversationReadAction::class);

    $state = $markRead->execute($recipient, $conversation, $second);
    $state = $markRead->execute($recipient, $conversation, $first);

    expect($state->last_read_message_id)->toBe($second->id)
        ->and($conversation->fresh(['latestMessage', 'states'])->isUnreadFor($recipient))->toBeFalse();

    [, , $otherConversation] = directMessageConversation();
    $foreignMessage = DirectMessage::factory()->create([
        'direct_conversation_id' => $otherConversation->id,
        'sender_id' => $otherConversation->participant_one_id,
    ]);

    $this->expectException(ValidationException::class);
    $markRead->execute($recipient, $conversation, $foreignMessage);
});

test('message validation rejects blank and oversized plaintext before storage', function () {
    [$sender, , $conversation] = directMessageConversation();
    $send = app(SendDirectMessageAction::class);

    try {
        $send->execute($sender, $conversation, '   ');
        $this->fail('Blank direct messages must be rejected.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('body');
    }

    $this->expectException(ValidationException::class);
    $send->execute($sender, $conversation, str_repeat('a', 4001));
});
