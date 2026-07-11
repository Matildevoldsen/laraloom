<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('mobile members can reply to posts and to existing replies', function () {
    $member = User::factory()->create();
    $post = Post::factory()->create();
    Sanctum::actingAs($member, ['mobile']);

    $parent = $this->postJson("/api/v1/posts/{$post->id}/comments", [
        'body' => 'This is useful context.',
    ])->assertCreated()->assertJsonPath('data.author.id', $member->id);

    $parentId = $parent->json('data.id');

    $this->postJson("/api/v1/posts/{$post->id}/comments", [
        'body' => 'A nested reply.',
        'parent_id' => $parentId,
    ])->assertCreated()->assertJsonPath('data.parent_id', $parentId);

    $this->getJson("/api/v1/posts/{$post->id}/comments")
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.replies_count', 1);
});

test('a reply parent must belong to the same post', function () {
    $member = User::factory()->create();
    $post = Post::factory()->create();
    $otherComment = Comment::factory()->create();
    Sanctum::actingAs($member, ['mobile']);

    $this->postJson("/api/v1/posts/{$post->id}/comments", [
        'body' => 'Wrong thread.',
        'parent_id' => $otherComment->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('parent_id');
});

test('comment deletion is limited to its owner and admins', function () {
    $owner = User::factory()->create();
    $comment = Comment::factory()->for($owner)->create();
    Sanctum::actingAs(User::factory()->create(), ['mobile']);

    $this->deleteJson("/api/v1/comments/{$comment->id}")->assertForbidden();

    Sanctum::actingAs($owner, ['mobile']);
    $this->deleteJson("/api/v1/comments/{$comment->id}")->assertNoContent();

    $admin = User::factory()->create(['is_admin' => true]);
    $secondComment = Comment::factory()->create();
    Sanctum::actingAs($admin, ['mobile']);
    $this->deleteJson("/api/v1/comments/{$secondComment->id}")->assertNoContent();
});

test('reposts toggle and surface posts in the following feed', function () {
    $viewer = User::factory()->create();
    $builder = User::factory()->create();
    $originalAuthor = User::factory()->create();
    $post = Post::factory()->for($originalAuthor)->create();
    $viewer->following()->attach($builder);

    Sanctum::actingAs($builder, ['mobile']);
    $this->postJson("/api/v1/posts/{$post->id}/repost")
        ->assertOk()
        ->assertExactJson(['active' => true, 'count' => 1]);

    Sanctum::actingAs($viewer, ['mobile']);
    $this->getJson('/api/v1/feed/following')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $post->id)
        ->assertJsonPath('data.0.counts.reposts', 1);

    Sanctum::actingAs($builder, ['mobile']);
    $this->postJson("/api/v1/posts/{$post->id}/repost")
        ->assertOk()
        ->assertExactJson(['active' => false, 'count' => 0]);
});

test('web members can reply and repost from the same conversation', function () {
    $member = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($member)
        ->post(route('posts.comments.store', $post), ['body' => 'Useful context.'])
        ->assertRedirect(route('posts.show', $post));

    $this->actingAs($member)
        ->from(route('posts.show', $post))
        ->post(route('posts.repost', $post))
        ->assertRedirect(route('posts.show', $post));

    $this->assertDatabaseHas('comments', [
        'post_id' => $post->id,
        'user_id' => $member->id,
        'body' => 'Useful context.',
    ]);
    $this->assertDatabaseHas('reposts', [
        'post_id' => $post->id,
        'user_id' => $member->id,
    ]);
});
