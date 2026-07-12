<?php

use App\Events\FollowChanged;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

it('broadcasts follow changes to the viewed profile channel', function () {
    $follower = User::factory()->create();
    $creator = User::factory()->create();
    Event::fake([FollowChanged::class]);

    $this->actingAs($follower)->post(route('profiles.follow', $creator))->assertRedirect();

    Event::assertDispatched(FollowChanged::class, function (FollowChanged $event) use ($follower, $creator): bool {
        return $event->followerId === $follower->id
            && $event->followingId === $creator->id
            && $event->isFollowing
            && $event->broadcastAs() === 'follow.changed'
            && $event->broadcastOn()[0] instanceof Channel
            && $event->broadcastOn()[0]->name === "sourcefolk.profiles.{$creator->id}";
    });
});

it('returns a complete authenticated public profile', function () {
    $viewer = User::factory()->create();
    $creator = User::factory()->create();
    $post = Post::factory()->for($creator)->create();
    $project = Project::factory()->for($creator)->create();
    $likedPost = Post::factory()->create();
    $repostedPost = Post::factory()->create();
    $reply = Comment::factory()->for($creator)->for($post)->create();
    $creator->reactedPosts()->attach($likedPost);
    $creator->repostedPosts()->attach($repostedPost);
    $viewer->following()->attach($creator);
    Sanctum::actingAs($viewer, ['mobile']);

    $this->getJson(route('api.v1.profiles.show', $creator))
        ->assertOk()
        ->assertJsonPath('data.is_following', true)
        ->assertJsonPath('data.posts.0.id', $post->id)
        ->assertJsonPath('data.projects.0.id', $project->id)
        ->assertJsonPath('data.replies.0.id', $reply->id)
        ->assertJsonPath('data.liked_posts.0.id', $likedPost->id)
        ->assertJsonPath('data.reposted_posts.0.id', $repostedPost->id)
        ->assertJsonPath('data.followers.0.id', $viewer->id)
        ->assertJsonPath('data.counts.followers', 1);
});

it('renders realtime profile state and accurate follow labels on web', function () {
    $viewer = User::factory()->create();
    $creator = User::factory()->create();
    $viewer->following()->attach($creator);

    $this->actingAs($viewer)->get(route('profiles.show', $creator))
        ->assertOk()
        ->assertSee('data-realtime-profile', escape: false)
        ->assertSee('data-profile-id="'.$creator->id.'"', escape: false)
        ->assertSee('Unfollow')
        ->assertSee('Replies')
        ->assertSee('Reposts')
        ->assertSee('Likes')
        ->assertSee('Packages');
});
