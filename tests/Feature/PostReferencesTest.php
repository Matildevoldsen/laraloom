<?php

use App\Actions\StorePostAttachmentsAction;
use App\Data\StoredPostAttachments;
use App\Models\Post;
use App\Models\User;
use App\PostKind;
use Illuminate\Support\Facades\DB;

test('publishing extracts hashtags and resolved mentions into their own models', function (): void {
    $author = User::factory()->create(['username' => 'author']);
    $mentioned = User::factory()->create(['username' => 'maker-one']);

    $this->actingAs($author)->post(route('posts.store'), [
        'kind' => PostKind::Note->value,
        'body' => 'Hello @maker-one @maker-one @missing and @author. #Laravel #laravel #Läravel_13',
    ])->assertRedirect(route('home'));

    $post = Post::query()->with(['hashtags', 'mentions'])->sole();

    expect($post->hashtags->pluck('slug')->sort()->values()->all())
        ->toBe(['laravel', 'läravel_13'])
        ->and($post->hashtags->keyBy('slug')->get('laravel')?->name)->toBe('Laravel')
        ->and($post->mentions->pluck('handle')->sort()->values()->all())
        ->toBe(['author', 'maker-one'])
        ->and($mentioned->notifications()->count())->toBe(1)
        ->and($mentioned->notifications()->sole()->data)->toMatchArray([
            'kind' => 'mention',
            'actor_id' => $author->id,
            'post_id' => $post->id,
            'url' => route('posts.show', $post, absolute: false),
        ])
        ->and($author->notifications()->count())->toBe(0);

    $this->actingAs($mentioned)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('mentioned you in a post');
});

test('editing synchronizes references and only new mentions notify', function (): void {
    $author = User::factory()->create(['username' => 'author']);
    $firstMember = User::factory()->create(['username' => 'first_member']);
    $secondMember = User::factory()->create(['username' => 'second-member']);

    $this->actingAs($author)->post(route('posts.store'), [
        'kind' => PostKind::Note->value,
        'body' => 'Original @first_member #Laravel',
    ])->assertRedirect();

    $post = Post::query()->sole();

    $this->actingAs($author)->put(route('posts.update', $post), [
        'kind' => PostKind::Note->value,
        'body' => 'Edited @first_member twice @first_member plus @second-member #Livewire',
    ])->assertRedirect(route('home'));

    $post->refresh()->load(['hashtags', 'mentions']);

    expect($post->mentions->pluck('handle')->sort()->values()->all())
        ->toBe(['first_member', 'second-member'])
        ->and($post->hashtags->pluck('slug')->all())->toBe(['livewire'])
        ->and($firstMember->notifications()->count())->toBe(1)
        ->and($secondMember->notifications()->count())->toBe(1);

    $this->actingAs($author)->put(route('posts.update', $post), [
        'kind' => PostKind::Note->value,
        'body' => 'Only @second-member remains #Livewire',
    ])->assertRedirect(route('home'));

    expect($post->mentions()->sole()->mentioned_user_id)->toBe($secondMember->id)
        ->and($secondMember->notifications()->count())->toBe(1);
});

test('existing posts can be backfilled without sending historical mention notifications', function (): void {
    $author = User::factory()->create(['username' => 'author']);
    $mentioned = User::factory()->create(['username' => 'existing_member']);
    $post = Post::factory()->for($author)->create([
        'body' => 'An existing post for @existing_member and #Laravel.',
    ]);

    $this->artisan('posts:sync-references', ['--chunk' => 1])
        ->expectsOutputToContain('Synchronized references for 1 posts.')
        ->assertSuccessful();

    expect($post->hashtags()->sole()->slug)->toBe('laravel')
        ->and($post->mentions()->sole()->mentioned_user_id)->toBe($mentioned->id)
        ->and($mentioned->notifications()->count())->toBe(0);
});

test('external attachment storage runs before the post transaction begins', function (): void {
    $author = User::factory()->create(['username' => 'author']);
    $mentioned = User::factory()->create(['username' => 'outside_transaction']);
    $storeAttachments = Mockery::mock(StorePostAttachmentsAction::class);
    $baseTransactionLevel = DB::transactionLevel();

    $storeAttachments->shouldReceive('execute')
        ->once()
        ->withArgs(function (User $user, array $files) use ($author, $baseTransactionLevel): bool {
            expect(DB::transactionLevel())->toBe($baseTransactionLevel)
                ->and(Post::query()->doesntExist())->toBeTrue();

            return $user->is($author) && $files === [];
        })
        ->andReturn(new StoredPostAttachments([]));
    $storeAttachments->shouldReceive('delete')->never();

    $this->app->instance(StorePostAttachmentsAction::class, $storeAttachments);

    $this->actingAs($author)->post(route('posts.store'), [
        'kind' => PostKind::Note->value,
        'body' => 'A post for @outside_transaction.',
    ])->assertRedirect(route('home'));

    expect(Post::query()->sole()->mentions()->where('mentioned_user_id', $mentioned->id)->exists())
        ->toBeTrue();
});
