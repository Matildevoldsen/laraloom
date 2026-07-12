<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\User;

beforeEach(function (): void {
    config()->set('app.name', 'Sourcefolk');
    config()->set('app.url', 'https://sourcefolk.com');
});

test('the public feed exposes complete social metadata', function (): void {
    $response = $this->get('https://sourcefolk.com/');

    $response
        ->assertOk()
        ->assertSee('<title>Sourcefolk — Everything happening in Laravel</title>', false)
        ->assertSee('<link rel="canonical" href="https://sourcefolk.com"', false)
        ->assertSee('<meta property="og:title" content="Sourcefolk — Everything happening in Laravel"', false)
        ->assertSee('<meta property="og:image" content="https://sourcefolk.com/social/sourcefolk-card.png"', false)
        ->assertSee('<meta property="og:image:width" content="1200"', false)
        ->assertSee('<meta property="og:image:height" content="630"', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image"', false)
        ->assertSee('<meta name="twitter:image" content="https://sourcefolk.com/social/sourcefolk-card.png"', false)
        ->assertSee('<link rel="manifest" href="https://sourcefolk.com/site.webmanifest"', false);
});

test('posts expose useful page specific titles and descriptions', function (): void {
    $post = Post::factory()->create([
        'title' => 'A sharper Laravel queue pattern',
        'why_it_matters' => 'A practical way to keep queued work observable.',
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertSee('<title>A sharper Laravel queue pattern — Sourcefolk</title>', false)
        ->assertSee('content="A practical way to keep queued work observable."', false);
});

test('profiles and projects expose specific social metadata', function (): void {
    $user = User::factory()->create([
        'name' => 'Taylor Maker',
        'username' => 'taylormaker',
        'bio' => 'Building thoughtful tools for Laravel developers.',
    ]);
    $project = Project::factory()->for($user)->create([
        'name' => 'Signal Kit',
        'tagline' => 'Realtime Laravel collaboration without the noise.',
        'screenshot_url' => 'https://cdn.example.com/signal-kit.png',
    ]);

    $this->get(route('profiles.show', $user))
        ->assertOk()
        ->assertSee('<title>Taylor Maker (@taylormaker) — Sourcefolk</title>', false)
        ->assertSee('content="Building thoughtful tools for Laravel developers."', false);

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('<title>Signal Kit — Sourcefolk</title>', false)
        ->assertSee('content="Realtime Laravel collaboration without the noise."', false)
        ->assertSee('<meta property="og:image" content="https://cdn.example.com/signal-kit.png"', false);
});

test('sourcefolk icon and social card assets have production dimensions', function (): void {
    $socialCard = getimagesize(public_path('social/sourcefolk-card.png'));
    $touchIcon = getimagesize(public_path('apple-touch-icon.png'));
    $appIcon = getimagesize(public_path('icon-512.png'));

    expect(public_path('favicon.svg'))->toBeFile()
        ->and(public_path('favicon.ico'))->toBeFile()
        ->and($socialCard)->not->toBeFalse()
        ->and($socialCard[0])->toBe(1200)
        ->and($socialCard[1])->toBe(630)
        ->and($touchIcon)->not->toBeFalse()
        ->and($touchIcon[0])->toBe(180)
        ->and($touchIcon[1])->toBe(180)
        ->and($appIcon)->not->toBeFalse()
        ->and($appIcon[0])->toBe(512)
        ->and($appIcon[1])->toBe(512);
});
