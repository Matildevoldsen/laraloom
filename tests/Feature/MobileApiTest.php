<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\PostStatus;
use App\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('the public mobile feed only returns published posts', function () {
    $published = Post::factory()->create(['title' => 'Laravel Native Today']);
    Post::factory()->create([
        'status' => PostStatus::Pending,
        'published_at' => null,
        'title' => 'Private draft',
    ]);

    $this->getJson('/api/v1/feed')
        ->assertOk()
        ->assertJsonPath('data.0.id', $published->id)
        ->assertJsonPath('data.0.title', 'Laravel Native Today')
        ->assertJsonCount(1, 'data');
});

test('a published post can be opened from the mobile feed', function () {
    $post = Post::factory()->create();

    $this->getJson("/api/v1/posts/{$post->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $post->id)
        ->assertJsonPath('data.author.id', $post->user_id);
});

test('the projects API returns published projects by slug', function () {
    $project = Project::factory()->create(['slug' => 'laraloom-ios']);
    Project::factory()->create(['status' => ProjectStatus::Pending]);

    $this->getJson('/api/v1/projects')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->getJson('/api/v1/projects/laraloom-ios')
        ->assertOk()
        ->assertJsonPath('data.id', $project->id);
});

test('a mobile client can exchange credentials for a scoped token', function () {
    $user = User::factory()->create([
        'email' => 'taylor@example.com',
        'password' => 'secret-password',
    ]);

    $response = $this->postJson('/api/v1/auth/token', [
        'email' => 'taylor@example.com',
        'password' => 'secret-password',
        'device_name' => 'iPhone 17 Pro',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonStructure(['token', 'user']);

    expect($user->tokens()->first()?->can('mobile'))->toBeTrue();
});

test('invalid mobile credentials are rejected without creating a token', function () {
    $user = User::factory()->create(['password' => 'correct-password']);

    $this->postJson('/api/v1/auth/token', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'iPhone',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');

    expect($user->tokens()->count())->toBe(0);
});

test('the following feed requires a mobile token and filters authors', function () {
    $viewer = User::factory()->create();
    $followed = User::factory()->create();
    $other = User::factory()->create();
    $viewer->following()->attach($followed);
    $visible = Post::factory()->for($followed)->create();
    Post::factory()->for($other)->create();

    $this->getJson('/api/v1/feed/following')->assertUnauthorized();

    Sanctum::actingAs($viewer, ['mobile']);

    $this->getJson('/api/v1/feed/following')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $visible->id);
});

test('mobile interactions toggle and return authoritative counts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $creator = User::factory()->create();
    Sanctum::actingAs($user, ['mobile']);

    $this->postJson("/api/v1/posts/{$post->id}/reaction")
        ->assertOk()
        ->assertExactJson(['active' => true, 'count' => 1]);

    $this->postJson("/api/v1/posts/{$post->id}/bookmark")
        ->assertOk()
        ->assertExactJson(['active' => true, 'count' => 1]);

    $this->postJson("/api/v1/profiles/{$creator->username}/follow")
        ->assertOk()
        ->assertExactJson(['active' => true, 'count' => 1]);

    $this->postJson("/api/v1/posts/{$post->id}/reaction")
        ->assertOk()
        ->assertExactJson(['active' => false, 'count' => 0]);
});

test('a mobile user can publish a community post', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['mobile']);

    $this->postJson('/api/v1/posts', [
        'kind' => 'note',
        'body' => 'I shipped a NativePHP app today.',
        'tags' => 'NativePHP, Laravel',
    ])->assertCreated()
        ->assertJsonPath('data.author.id', $user->id)
        ->assertJsonPath('data.kind', 'note');

    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'body' => 'I shipped a NativePHP app today.',
    ]);
});
