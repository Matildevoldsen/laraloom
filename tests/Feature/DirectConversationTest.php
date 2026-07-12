<?php

use App\Actions\SendDirectMessageAction;
use App\Actions\StartDirectConversationAction;
use App\Models\DirectConversation;
use App\Models\User;
use App\Policies\DirectConversationPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

test('only a follower may be selected as a new conversation recipient', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $policy = new DirectConversationPolicy;

    expect($policy->create($sender, $recipient))->toBeFalse()
        ->and($policy->create($sender, $sender))->toBeFalse()
        ->and($policy->create($admin, $recipient))->toBeFalse();

    $recipient->following()->attach($sender);

    expect($policy->create($sender, $recipient))->toBeTrue()
        ->and($policy->create($recipient, $sender))->toBeFalse();
});

test('starting a conversation creates one canonical pair and participant states', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $recipient->following()->attach($sender);
    $action = app(StartDirectConversationAction::class);

    $conversation = $action->execute($sender, $recipient);
    $sameConversation = $action->execute($sender, $recipient);

    expect($sameConversation->is($conversation))->toBeTrue()
        ->and($conversation->participantIds())->toBe(
            collect([$sender->id, $recipient->id])->sort()->values()->all(),
        )
        ->and($conversation->initiated_by_id)->toBe($sender->id)
        ->and($conversation->states)->toHaveCount(2)
        ->and($conversation->states->pluck('user_id')->sort()->values()->all())
        ->toBe(collect([$sender->id, $recipient->id])->sort()->values()->all());

    $this->assertDatabaseCount('direct_conversations', 1);
    $this->assertDatabaseCount('direct_conversation_states', 2);
});

test('the start action enforces follower authorization itself', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    $this->expectException(AuthorizationException::class);
    app(StartDirectConversationAction::class)->execute($sender, $recipient);
});

test('both participants may reply while either follow direction remains', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $recipient->following()->attach($sender);
    $conversation = app(StartDirectConversationAction::class)->execute($sender, $recipient);
    $send = app(SendDirectMessageAction::class);

    $first = $send->execute($sender, $conversation, 'Hello from the person being followed.');
    $reply = $send->execute($recipient, $conversation, 'A reply from the follower.');

    expect($first->sender_id)->toBe($sender->id)
        ->and($reply->sender_id)->toBe($recipient->id)
        ->and($conversation->refresh()->messages)->toHaveCount(2);

    $recipient->following()->detach($sender);
    $sender->following()->attach($recipient);

    expect(Gate::forUser($sender)->allows('send', $conversation))->toBeTrue()
        ->and(Gate::forUser($recipient)->allows('send', $conversation))->toBeTrue();
});

test('an unfollowed conversation becomes read only without hiding its history', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $recipient->following()->attach($sender);
    $conversation = app(StartDirectConversationAction::class)->execute($sender, $recipient);
    app(SendDirectMessageAction::class)->execute($sender, $conversation, 'This history remains visible.');

    $recipient->following()->detach($sender);

    expect(Gate::forUser($sender)->allows('view', $conversation))->toBeTrue()
        ->and(Gate::forUser($recipient)->allows('view', $conversation))->toBeTrue()
        ->and(Gate::forUser($sender)->denies('send', $conversation))->toBeTrue()
        ->and($conversation->messages()->firstOrFail()->body)->toBe('This history remains visible.');

    $this->expectException(AuthorizationException::class);
    app(SendDirectMessageAction::class)->execute($recipient, $conversation, 'This must be rejected.');
});

test('outsiders and administrators receive no direct message policy bypass', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();
    $outsider = User::factory()->create();
    $admin = User::factory()->create(['is_admin' => true]);
    $second->following()->attach($first);
    $conversation = app(StartDirectConversationAction::class)->execute($first, $second);

    expect(Gate::forUser($first)->allows('view', $conversation))->toBeTrue()
        ->and(Gate::forUser($second)->allows('view', $conversation))->toBeTrue()
        ->and(Gate::forUser($outsider)->denies('view', $conversation))->toBeTrue()
        ->and(Gate::forUser($admin)->denies('view', $conversation))->toBeTrue()
        ->and(Gate::forUser($admin)->denies('markRead', $conversation))->toBeTrue();
});

test('deleting either participant cascades the private conversation history', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $recipient->following()->attach($sender);
    $conversation = app(StartDirectConversationAction::class)->execute($sender, $recipient);
    app(SendDirectMessageAction::class)->execute($sender, $conversation, 'Delete with the account.');

    $sender->delete();

    $this->assertDatabaseMissing('direct_conversations', ['id' => $conversation->id]);
    $this->assertDatabaseCount('direct_messages', 0);
    $this->assertDatabaseCount('direct_conversation_states', 0);
});

test('the web inbox lets a follower start and both participants open a conversation', function () {
    $sender = User::factory()->create(['name' => 'Message Sender']);
    $recipient = User::factory()->create(['name' => 'Message Recipient']);
    $recipient->following()->attach($sender);

    $response = $this->actingAs($sender)
        ->post(route('direct-messages.store', ['recipient' => $recipient]));

    $conversation = DirectConversation::query()->firstOrFail();
    $response->assertRedirect(route('direct-messages.show', $conversation));

    app(SendDirectMessageAction::class)->execute(
        $sender,
        $conversation,
        'A private hello from the web.',
    );

    $this->actingAs($recipient)
        ->get(route('direct-messages.show', $conversation))
        ->assertOk()
        ->assertSee('Message Sender')
        ->assertSee('A private hello from the web.')
        ->assertSee('not end-to-end encrypted');
});

test('an outsider cannot open another users web conversation', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $outsider = User::factory()->create();
    $recipient->following()->attach($sender);
    $conversation = app(StartDirectConversationAction::class)->execute($sender, $recipient);

    $this->actingAs($outsider)
        ->get(route('direct-messages.show', $conversation))
        ->assertForbidden();
});

test('an open web thread can mark the latest incoming message as read without navigation', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();
    $recipient->following()->attach($sender);
    $conversation = app(StartDirectConversationAction::class)->execute($sender, $recipient);
    $message = app(SendDirectMessageAction::class)->execute($sender, $conversation, 'Read this live.');

    $this->actingAs($recipient)
        ->putJson(route('direct-messages.read', $conversation))
        ->assertNoContent();

    $this->assertDatabaseHas('direct_conversation_states', [
        'direct_conversation_id' => $conversation->id,
        'user_id' => $recipient->id,
        'last_read_message_id' => $message->id,
    ]);
});
