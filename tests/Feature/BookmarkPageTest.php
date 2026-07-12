<?php

use App\Models\Post;
use App\Models\User;
use App\PostStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\CursorPaginator;

uses(RefreshDatabase::class);

test('guests cannot view bookmarks', function (): void {
    $this->get(route('bookmarks.index'))->assertRedirect(route('login'));
});

test('members see only their published bookmarks in bookmark order', function (): void {
    $member = User::factory()->create();
    $otherMember = User::factory()->create();
    $olderBookmark = Post::factory()->create(['title' => 'Older bookmark']);
    $newerBookmark = Post::factory()->create(['title' => 'Newer bookmark']);
    $anotherMembersBookmark = Post::factory()->create(['title' => 'Private to another member']);
    $draft = Post::factory()->create([
        'status' => PostStatus::Draft,
        'title' => 'Bookmarked draft',
    ]);
    $scheduled = Post::factory()->create([
        'published_at' => now()->addHour(),
        'title' => 'Bookmarked scheduled post',
    ]);

    $member->bookmarkedPosts()->attach($olderBookmark, [
        'created_at' => now()->subHour(),
        'updated_at' => now()->subHour(),
    ]);
    $member->bookmarkedPosts()->attach($newerBookmark, [
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $member->bookmarkedPosts()->attach([$draft->id, $scheduled->id]);
    $otherMember->bookmarkedPosts()->attach($anotherMembersBookmark);

    $response = $this->actingAs($member)->get(route('bookmarks.index'));

    $response
        ->assertSuccessful()
        ->assertViewIs('bookmarks.index')
        ->assertViewHas('posts', function (CursorPaginator $posts) use ($newerBookmark, $olderBookmark): bool {
            return $posts->pluck('id')->all() === [$newerBookmark->id, $olderBookmark->id]
                && $posts->every(fn (Post $post): bool => $post->is_bookmarked);
        })
        ->assertSeeTextInOrder(['Newer bookmark', 'Older bookmark'])
        ->assertDontSeeText('Private to another member')
        ->assertDontSeeText('Bookmarked draft')
        ->assertDontSeeText('Bookmarked scheduled post')
        ->assertSee('aria-label="Bookmarks"', false)
        ->assertSee('data-realtime-feed', false);
});

test('bookmark pages support infinite cursor pagination', function (): void {
    $member = User::factory()->create();
    $posts = Post::factory()->count(13)->create();

    foreach ($posts as $index => $post) {
        $member->bookmarkedPosts()->attach($post, [
            'created_at' => now()->subMinutes($index),
            'updated_at' => now()->subMinutes($index),
        ]);
    }

    $firstPage = $this->actingAs($member)->get(route('bookmarks.index'));

    $firstPage
        ->assertSuccessful()
        ->assertViewHas('posts', fn (CursorPaginator $bookmarkedPosts): bool => $bookmarkedPosts->count() === 12 && $bookmarkedPosts->hasMorePages())
        ->assertSee('data-infinite-feed', false);

    /** @var CursorPaginator<int, Post> $bookmarkedPosts */
    $bookmarkedPosts = $firstPage->viewData('posts');

    $this->get($bookmarkedPosts->nextPageUrl())
        ->assertSuccessful()
        ->assertViewHas('posts', fn (CursorPaginator $nextPage): bool => $nextPage->count() === 1 && ! $nextPage->hasMorePages())
        ->assertDontSee('data-infinite-feed', false);
});
