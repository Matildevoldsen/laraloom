<?php

use App\CommunityActivityType;
use App\Events\CommunityActivity;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Observers\CommunityActivityObserver;
use App\PostStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

it('broadcasts public activity to the feed conversation and admin channels', function () {
    $event = new CommunityActivity(CommunityActivityType::CommentCreated, 42, true);

    expect($event->broadcastAs())->toBe('community.activity')
        ->and(collect($event->broadcastOn())->map(fn (Channel $channel): string => $channel->name)->all())
        ->toBe(['private-sourcefolk.admin', 'sourcefolk.feed', 'sourcefolk.posts.42'])
        ->and($event->broadcastWith())->toMatchArray([
            'type' => 'comment.created',
            'post_id' => 42,
        ]);
});

it('keeps unpublished activity on the private admin channel', function () {
    $event = new CommunityActivity(CommunityActivityType::PostUpdated, 7, false);

    expect($event->broadcastOn())->toHaveCount(1)
        ->and($event->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class)
        ->and($event->broadcastOn()[0]->name)->toBe('private-sourcefolk.admin');
});

it('maps model activity to a typed broadcast event', function () {
    Event::fake([CommunityActivity::class]);
    $post = Post::factory()->create();
    $comment = Comment::factory()->for($post)->create();
    request()->headers->set('X-Socket-ID', '123.456');

    app(CommunityActivityObserver::class)->created($comment);

    Event::assertDispatched(
        CommunityActivity::class,
        fn (CommunityActivity $event): bool => $event->type === CommunityActivityType::CommentCreated
            && $event->postId === $post->id
            && $event->isPublic
            && $event->socket === '123.456',
    );
});

it('does not expose pending posts on public channels', function () {
    Event::fake([CommunityActivity::class]);
    $post = Post::factory()->create([
        'status' => PostStatus::Pending,
        'published_at' => null,
    ]);

    app(CommunityActivityObserver::class)->created($post);

    Event::assertDispatched(
        CommunityActivity::class,
        fn (CommunityActivity $event): bool => ! $event->isPublic,
    );
});

it('requires a mobile token for native channel authentication', function () {
    $this->postJson(route('api.v1.broadcasting.auth'), [
        'channel_name' => 'private-sourcefolk.admin',
        'socket_id' => '1.2',
    ])->assertUnauthorized();
});

it('authorizes administrators for native moderation updates', function () {
    config()->set('broadcasting.default', 'pusher');
    config()->set('broadcasting.connections.pusher', [
        'driver' => 'pusher',
        'key' => 'test-key',
        'secret' => 'test-secret',
        'app_id' => 'test-key',
        'options' => ['cluster' => 'mt1'],
    ]);
    Broadcast::purge('pusher');
    require base_path('routes/channels.php');

    Sanctum::actingAs(User::factory()->create(['is_admin' => true]), ['mobile']);

    $this->postJson(route('api.v1.broadcasting.auth'), [
        'channel_name' => 'private-sourcefolk.admin',
        'socket_id' => '1.2',
    ])->assertOk()->assertJsonStructure(['auth']);
});
