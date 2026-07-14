<?php

use App\Models\Post;
use App\Models\User;
use App\PostKind;

test('hashtags are searchable globally and have a published post feed', function (): void {
    $author = User::factory()->create(['username' => 'search_author']);

    $this->actingAs($author)->post(route('posts.store'), [
        'kind' => PostKind::Note->value,
        'title' => 'Visible hashtag post',
        'body' => 'A useful update for #Laravel developers.',
    ])->assertRedirect();

    $post = Post::query()->with('hashtags')->sole();
    $hashtag = $post->hashtags->sole();

    $this->get(route('home', ['q' => '#laravel']))
        ->assertOk()
        ->assertSee('Visible hashtag post');

    $this->get(route('hashtags.show', $hashtag))
        ->assertOk()
        ->assertSee('#Laravel')
        ->assertSee('1 published post')
        ->assertSee('Visible hashtag post');
});

test('post bodies render safe links for hashtags and resolved mentions', function (): void {
    $author = User::factory()->create(['username' => 'author']);
    $mentioned = User::factory()->create(['username' => 'maker-one']);

    $this->actingAs($author)->post(route('posts.store'), [
        'kind' => PostKind::Note->value,
        'body' => '<script>alert(1)</script> Hello @maker-one and #Laravel',
    ])->assertRedirect();

    $post = Post::query()->with('hashtags')->sole();
    $hashtag = $post->hashtags->sole();

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', escape: false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', escape: false)
        ->assertSee('class="social-token"', escape: false)
        ->assertSee(route('profiles.show', $mentioned), escape: false)
        ->assertSee(route('hashtags.show', $hashtag), escape: false);
});

test('the api exposes social references and searches hashtags', function (): void {
    $author = User::factory()->create(['username' => 'api_author']);
    $mentioned = User::factory()->create(['username' => 'api_member']);

    $this->actingAs($author)->post(route('posts.store'), [
        'kind' => PostKind::Note->value,
        'body' => 'API search @api_member #Livewire',
    ])->assertRedirect();

    $this->getJson(route('api.v1.feed', ['q' => '#livewire']))
        ->assertOk()
        ->assertJsonPath('data.0.hashtags.0.slug', 'livewire')
        ->assertJsonPath('data.0.mentions.0.handle', $mentioned->username)
        ->assertJsonPath('data.0.mentions.0.user.username', $mentioned->username);
});

test('unknown hashtags return a not found response', function (): void {
    $this->get('/hashtags/not-real')->assertNotFound();
});
