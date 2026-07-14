<?php

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use App\PostStatus;
use Livewire\Livewire;

test('the composer uses a livewire autocomplete with accessible textarea controls', function (): void {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('data-composer-textarea', escape: false)
        ->assertSee('composerAutocomplete($wire)', escape: false)
        ->assertSee('composerHighlighter', escape: false)
        ->assertSee('data-composer-editor', escape: false)
        ->assertSee('role="listbox"', escape: false)
        ->assertSee('popover="manual"', escape: false)
        ->assertSee('Couldn’t load suggestions. Keep typing to retry.');
});

test('mention suggestions match username prefixes', function (): void {
    $author = User::factory()->create(['username' => 'sourcefolk']);
    $member = User::factory()->create(['username' => 'realtime_qa']);

    $this->actingAs($author);

    Livewire::test('composer-autocomplete')
        ->call('suggest', '@', 'realti')
        ->assertReturned(fn (array $suggestions): bool => array_column($suggestions, 'id') === [$member->id]);
});

test('mention suggestions prioritize followed members and exclude the author', function (): void {
    $author = User::factory()->create(['username' => 'laravel_author']);
    $followed = User::factory()->create(['name' => 'Laravel Friend', 'username' => 'laravel_friend']);
    $verified = User::factory()->verified()->create(['name' => 'Laravel Verified', 'username' => 'laravel_verified']);
    $author->following()->attach($followed);

    $this->actingAs($author);

    Livewire::test('composer-autocomplete')
        ->call('suggest', '@', 'laravel')
        ->assertReturned(function (array $suggestions) use ($author, $followed, $verified): bool {
            return array_column($suggestions, 'id') === [$followed->id, $verified->id]
                && ! in_array($author->id, array_column($suggestions, 'id'), true)
                && $suggestions[0]['replacement'] === '@laravel_friend';
        });
});

test('hashtag suggestions rank published usage and hide unpublished tags', function (): void {
    $member = User::factory()->create();
    $popular = Hashtag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $specific = Hashtag::factory()->create(['name' => 'Laravel AI', 'slug' => 'laravel_ai']);
    $hidden = Hashtag::factory()->create(['name' => 'Laravel Secret', 'slug' => 'laravel_secret']);

    $popular->posts()->attach(Post::factory()->count(2)->create());
    $specific->posts()->attach(Post::factory()->create());
    $hidden->posts()->attach(Post::factory()->create([
        'status' => PostStatus::Rejected,
        'published_at' => null,
    ]));

    $this->actingAs($member);

    Livewire::test('composer-autocomplete')
        ->call('suggest', '#', 'laravel')
        ->assertReturned(function (array $suggestions) use ($popular, $specific, $hidden): bool {
            return array_column($suggestions, 'id') === [$popular->id, $specific->id]
                && ! in_array($hidden->id, array_column($suggestions, 'id'), true)
                && $suggestions[0]['description'] === '2 posts';
        });
});

test('bare triggers are accepted while guests and invalid triggers are rejected', function (): void {
    $member = User::factory()->create();
    User::factory()->create(['username' => 'suggested_member']);

    $this->actingAs($member);

    Livewire::test('composer-autocomplete')
        ->call('suggest', '@', '')
        ->assertReturned(fn (array $suggestions): bool => count($suggestions) === 1);

    Livewire::test('composer-autocomplete')
        ->call('suggest', '$', 'invalid')
        ->assertStatus(422);

    auth()->logout();

    Livewire::test('composer-autocomplete')
        ->call('suggest', '@', '')
        ->assertStatus(401);
});
