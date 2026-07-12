<?php

use App\Models\Post;
use App\Models\User;
use App\PostStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('web interaction endpoints return authoritative json state', function (string $routeName, string $table) {
    $member = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($member)
        ->postJson(route($routeName, $post))
        ->assertOk()
        ->assertExactJson(['active' => true, 'count' => 1]);

    $this->assertDatabaseHas($table, [
        'post_id' => $post->id,
        'user_id' => $member->id,
    ]);

    $this->postJson(route($routeName, $post))
        ->assertOk()
        ->assertExactJson(['active' => false, 'count' => 0]);

    $this->assertDatabaseMissing($table, [
        'post_id' => $post->id,
        'user_id' => $member->id,
    ]);
})->with([
    'reaction' => ['posts.reaction', 'reactions'],
    'repost' => ['posts.repost', 'reposts'],
    'bookmark' => ['posts.bookmark', 'bookmarks'],
]);

test('web interaction endpoints do not expose unpublished posts', function (string $routeName, string $table) {
    $member = User::factory()->create();
    $post = Post::factory()->create([
        'status' => PostStatus::Pending,
        'published_at' => null,
    ]);

    $this->actingAs($member)
        ->postJson(route($routeName, $post))
        ->assertNotFound();

    $this->assertDatabaseMissing($table, [
        'post_id' => $post->id,
        'user_id' => $member->id,
    ]);
})->with([
    'reaction' => ['posts.reaction', 'reactions'],
    'repost' => ['posts.repost', 'reposts'],
    'bookmark' => ['posts.bookmark', 'bookmarks'],
]);

test('post cards expose accessible interaction state on every web surface', function () {
    $member = User::factory()->create();
    $author = User::factory()->create();
    $post = Post::factory()->for($author)->create();

    $member->reactedPosts()->attach($post);
    $member->repostedPosts()->attach($post);
    $member->bookmarkedPosts()->attach($post);

    $responses = [
        $this->actingAs($member)->get(route('home')),
        $this->get(route('posts.show', $post)),
        $this->get(route('profiles.show', $author)),
    ];

    foreach ($responses as $response) {
        $response
            ->assertOk()
            ->assertSee('data-post-action', false)
            ->assertSee('data-post-interactions', false)
            ->assertSee('data-refresh-url=', false)
            ->assertSee('data-action="reaction"', false)
            ->assertSee('data-action="repost"', false)
            ->assertSee('data-action="bookmark"', false)
            ->assertSee('aria-label="Unlike"', false)
            ->assertSee('aria-label="Undo repost"', false)
            ->assertSee('aria-label="Remove bookmark"', false)
            ->assertSee('M6 3.75h12v17l-6-3.75-6 3.75v-17Z', false)
            ->assertDontSee('⌑')
            ->assertSee('post-action-reaction is-active', false)
            ->assertSee('post-action-repost is-active', false)
            ->assertSee('post-action-bookmark is-active', false);
    }
});

test('post cards render inactive interaction controls for a new viewer', function () {
    $member = User::factory()->create();
    Post::factory()->create();

    $this->actingAs($member)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('aria-label="Like"', false)
        ->assertSee('aria-label="Repost"', false)
        ->assertSee('aria-label="Bookmark"', false)
        ->assertDontSee('post-action-reaction is-active', false)
        ->assertDontSee('post-action-repost is-active', false)
        ->assertDontSee('post-action-bookmark is-active', false);
});

test('post interactions update in place without navigation or scroll changes', function () {
    $javascript = file_get_contents(resource_path('js/post-actions.js'));

    expect($javascript)
        ->toContain('event.preventDefault()')
        ->toContain('await fetch(form.action')
        ->toContain('renderMatchingActions(form, result.active, result.count)')
        ->not->toContain('window.location')
        ->not->toContain('location.reload')
        ->not->toContain('scrollTo(')
        ->not->toContain('scrollIntoView(');
});
