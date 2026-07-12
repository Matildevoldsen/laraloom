<?php

use App\Models\Post;

it('marks feed and post fragments for in-place realtime updates', function () {
    $post = Post::factory()->create(['title' => 'Realtime Laravel']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-realtime-feed', escape: false)
        ->assertSee('data-post-id="'.$post->id.'"', escape: false);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('data-realtime-post', escape: false)
        ->assertSee('data-realtime-conversation-summary', escape: false)
        ->assertSee('data-realtime-comments', escape: false);
});

it('refreshes visible fragments automatically when activity arrives', function () {
    $javascript = file_get_contents(resource_path('js/app.js'));

    expect($javascript)
        ->toContain('void refreshPostInteractions(activity.post_id)')
        ->toContain("replaceFragment(document, '[data-realtime-feed]')")
        ->toContain("replaceFragment(document, '[data-realtime-comments]')")
        ->not->toContain("listen('.community.activity', revealRefresh)");
});
