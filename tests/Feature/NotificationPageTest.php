<?php

use App\Broadcasting\UserNotificationsChannel;
use App\Events\CommunityNotificationCreated;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Repost;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Date;
use Livewire\Livewire;

test('guests cannot view notifications', function (): void {
    $this->get(route('notifications.index'))->assertRedirect(route('login'));
});

test('community interactions create notifications for their recipient', function (): void {
    $recipient = User::factory()->create();
    $actor = User::factory()->create(['name' => 'Taylor Otwell']);
    $post = Post::factory()->for($recipient)->create(['title' => 'A small framework idea']);

    Follow::factory()->create(['follower_id' => $actor->id, 'following_id' => $recipient->id]);
    Reaction::factory()->create(['user_id' => $actor->id, 'post_id' => $post->id]);
    Comment::factory()->create([
        'user_id' => $actor->id,
        'post_id' => $post->id,
        'body' => 'This is a thoughtful addition.',
    ]);
    Repost::factory()->create(['user_id' => $actor->id, 'post_id' => $post->id]);

    expect($recipient->notifications()->count())->toBe(4)
        ->and($recipient->notifications()->pluck('data')->pluck('kind')->sort()->values()->all())
        ->toBe(['comment', 'follow', 'reaction', 'repost']);

    $this->actingAs($recipient)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('Taylor Otwell')
        ->assertSee('liked your post')
        ->assertSee('commented on your post')
        ->assertSee('reposted your post')
        ->assertSee('followed you')
        ->assertSee('This is a thoughtful addition.')
        ->assertSee('Unread')
        ->assertSee('data-unread-count="4"', escape: false);
});

test('replies notify the comment author and post author without duplicates', function (): void {
    $postAuthor = User::factory()->create();
    $commentAuthor = User::factory()->create();
    $replyAuthor = User::factory()->create();
    $post = Post::factory()->for($postAuthor)->create();
    $parent = Comment::factory()->create([
        'user_id' => $commentAuthor->id,
        'post_id' => $post->id,
    ]);

    $postAuthor->notifications()->delete();
    Comment::factory()->create([
        'user_id' => $replyAuthor->id,
        'post_id' => $post->id,
        'parent_id' => $parent->id,
    ]);

    expect($commentAuthor->notifications()->sole()->data['kind'])->toBe('reply')
        ->and($postAuthor->notifications()->sole()->data['kind'])->toBe('comment');

    $postAuthor->notifications()->delete();
    $reply = Comment::factory()->create([
        'user_id' => $replyAuthor->id,
        'post_id' => $post->id,
        'parent_id' => Comment::factory()->create([
            'user_id' => $postAuthor->id,
            'post_id' => $post->id,
        ])->id,
    ]);

    expect($postAuthor->notifications()->count())->toBe(1)
        ->and($postAuthor->notifications()->sole()->data['kind'])->toBe('reply')
        ->and($postAuthor->notifications()->sole()->data['url'])->toEndWith("#comment-{$reply->id}");
});

test('members are not notified about their own activity', function (): void {
    $member = User::factory()->create();
    $post = Post::factory()->for($member)->create();

    Reaction::factory()->create(['user_id' => $member->id, 'post_id' => $post->id]);
    Comment::factory()->create(['user_id' => $member->id, 'post_id' => $post->id]);
    Repost::factory()->create(['user_id' => $member->id, 'post_id' => $post->id]);

    expect($member->notifications()->count())->toBe(0);
});

test('members can filter and mark their notifications as read', function (): void {
    $recipient = User::factory()->create();
    $actor = User::factory()->create();
    Follow::factory()->create(['follower_id' => $actor->id, 'following_id' => $recipient->id]);
    $notification = $recipient->notifications()->sole();

    $this->actingAs($recipient)
        ->get(route('notifications.index', ['filter' => 'unread']))
        ->assertSuccessful()
        ->assertSee($actor->name);

    $this->actingAs($recipient)
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('profiles.show', $actor, absolute: false));

    expect($notification->fresh()->read_at)->not->toBeNull();

    $this->actingAs($recipient)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('Read');

    Follow::factory()->create([
        'follower_id' => User::factory()->create()->id,
        'following_id' => $recipient->id,
    ]);

    $this->actingAs($recipient)
        ->patch(route('notifications.read-all'))
        ->assertRedirect();

    expect($recipient->unreadNotifications()->count())->toBe(0);
});

test('members cannot open another members notification', function (): void {
    $recipient = User::factory()->create();
    $outsider = User::factory()->create();
    Follow::factory()->create([
        'follower_id' => User::factory()->create()->id,
        'following_id' => $recipient->id,
    ]);

    $this->actingAs($outsider)
        ->post(route('notifications.read', $recipient->notifications()->sole()->id))
        ->assertNotFound();
});

test('notifications broadcast only on their recipients private channel', function (): void {
    $recipient = User::factory()->create();
    $outsider = User::factory()->create();
    $event = new CommunityNotificationCreated(
        $recipient->id,
        'notification-uuid',
        '2026-07-13T00:00:00+00:00',
    );
    $channel = $event->broadcastOn()[0];
    $authorizer = new UserNotificationsChannel;

    expect($channel)->toBeInstanceOf(PrivateChannel::class)
        ->and($channel->name)->toBe("private-sourcefolk.users.{$recipient->id}.notifications")
        ->and($event->broadcastAs())->toBe('notification.created')
        ->and($event->broadcastWith())->toBe([
            'user_id' => $recipient->id,
            'notification_id' => 'notification-uuid',
            'occurred_at' => '2026-07-13T00:00:00+00:00',
        ])
        ->and($authorizer->join($recipient, $recipient))->toBeTrue()
        ->and($authorizer->join($outsider, $recipient))->toBeFalse();
});

test('the notification center loads additional notifications on demand', function (): void {
    $recipient = User::factory()->create();
    $actors = User::factory()->count(25)->create();
    $startedAt = now();

    $actors->each(function (User $actor, int $index) use ($recipient, $startedAt): void {
        Date::setTestNow($startedAt->addSeconds($index));
        Follow::factory()->create([
            'follower_id' => $actor->id,
            'following_id' => $recipient->id,
        ]);
    });
    Date::setTestNow();

    $this->actingAs($recipient);

    Livewire::test('notification-center')
        ->assertSet('limit', 20)
        ->assertDontSee($actors->first()->name)
        ->assertSee($actors->last()->name)
        ->call('loadMore')
        ->assertSet('limit', 40)
        ->assertSee($actors->first()->name);
});

test('the notification center filters and marks notifications as read without a page reload', function (): void {
    $recipient = User::factory()->create();
    $actor = User::factory()->create();
    Follow::factory()->create([
        'follower_id' => $actor->id,
        'following_id' => $recipient->id,
    ]);

    $this->actingAs($recipient);

    Livewire::test('notification-center')
        ->assertSet('filter', 'all')
        ->call('showUnread')
        ->assertSet('filter', 'unread')
        ->assertSee($actor->name)
        ->call('markAllAsRead')
        ->assertDispatched('notifications-read')
        ->assertSee('You’re all caught up');

    expect($recipient->unreadNotifications()->count())->toBe(0);
});

test('the notification center prepends new activity when its private event arrives', function (): void {
    $recipient = User::factory()->create();
    $actor = User::factory()->create(['name' => 'Live Community Member']);
    $this->actingAs($recipient);
    $component = Livewire::test('notification-center')
        ->assertDontSee('Live Community Member');

    Follow::factory()->create([
        'follower_id' => $actor->id,
        'following_id' => $recipient->id,
    ]);
    $notification = $recipient->notifications()->sole();

    $component->call('notificationCreated', [
        'user_id' => $recipient->id,
        'notification_id' => $notification->id,
        'occurred_at' => now()->toIso8601String(),
    ])->assertSee('Live Community Member');
});

test('the favicon and browser title track the live unread count', function (): void {
    $recipient = User::factory()->create();
    $actor = User::factory()->create();
    Follow::factory()->create([
        'follower_id' => $actor->id,
        'following_id' => $recipient->id,
    ]);
    $this->actingAs($recipient);

    Livewire::test('notification-indicator')
        ->assertSet('unreadCount', 1)
        ->assertSeeHtml('data-unread-count="1"')
        ->call('notificationCreated', [
            'user_id' => $recipient->id,
            'notification_id' => 'new-notification-id',
            'occurred_at' => now()->toIso8601String(),
        ])
        ->assertSet('unreadCount', 2)
        ->assertSeeHtml('data-unread-count="2"');

    $javascript = file_get_contents(resource_path('js/notification-favicon.js'));

    expect($javascript)
        ->toContain("const indicatorSelector = '[data-notification-indicator]'")
        ->toContain('document.title = count > 0')
        ->toContain("context.fillStyle = '#ff4d73'")
        ->toContain('new MutationObserver(refreshNotificationFavicon)');
});
