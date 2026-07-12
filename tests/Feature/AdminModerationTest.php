<?php

use App\Models\Post;
use App\Models\User;
use App\PostStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('members cannot open the admin dashboard', function () {
    $member = User::factory()->create();

    $this->actingAs($member)->get('/admin')->assertForbidden();
});

test('admin controls are absent from a members community feed', function () {
    $member = User::factory()->create();
    Post::factory()->for($member)->create(['title' => 'A normal member post']);

    $this->actingAs($member)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Admin')
        ->assertDontSee('Moderate');
});

test('admin controls are visible to administrators', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    Post::factory()->create(['title' => 'A post needing review']);

    $this->actingAs($admin)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Admin')
        ->assertSee('Moderate');
});

test('an admin can review and moderate posts on the web', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $post = Post::factory()->create([
        'status' => PostStatus::Pending,
        'published_at' => null,
        'title' => 'Review this submission',
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Laraloom admin')
        ->assertSee('Review this submission');

    $this->actingAs($admin)
        ->patch(route('admin.posts.status', $post), [
            'status' => PostStatus::Published->value,
        ])
        ->assertRedirect();

    expect($post->refresh()->status)->toBe(PostStatus::Published)
        ->and($post->published_at)->not->toBeNull();
});

test('a member can edit their own post but not another members post', function () {
    $member = User::factory()->create();
    $ownPost = Post::factory()->for($member)->create();
    $otherPost = Post::factory()->create();

    $this->actingAs($member)
        ->put(route('posts.update', $ownPost), [
            'kind' => 'note',
            'body' => 'A more accurate update.',
            'tags' => 'Laravel',
        ])
        ->assertRedirect(route('home'));

    expect($ownPost->refresh()->body)->toBe('A more accurate update.');

    $this->actingAs($member)
        ->put(route('posts.update', $otherPost), [
            'kind' => 'note',
            'body' => 'Not allowed.',
        ])
        ->assertForbidden();
});
