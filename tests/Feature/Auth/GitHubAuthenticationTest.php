<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

test('GitHub is the primary action on the authentication screens', function () {
    foreach (['login', 'register'] as $routeName) {
        $this->get(route($routeName))
            ->assertOk()
            ->assertSeeInOrder(['Continue with GitHub', 'Email address'])
            ->assertSee(route('auth.github.redirect'), escape: false);
    }
});

test('a guest can start GitHub authentication', function () {
    Socialite::fake('github');

    $this->get(route('auth.github.redirect'))
        ->assertRedirect('https://socialite.fake/github/authorize');
});

test('a new user can create an account with GitHub', function () {
    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'github-123',
        'nickname' => 'octocat',
        'name' => 'The Octocat',
        'email' => 'octocat@example.com',
        'avatar' => 'https://avatars.githubusercontent.com/u/123',
    ]));

    $this->get(route('auth.github.callback'))
        ->assertRedirect(route('legal.acceptance.show'));

    $user = User::query()->where('github_id', 'github-123')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    expect($user)
        ->name->toBe('The Octocat')
        ->username->toBe('octocat')
        ->email->toBe('octocat@example.com')
        ->github_username->toBe('octocat')
        ->avatar_url->toBe('https://avatars.githubusercontent.com/u/123')
        ->email_verified_at->not->toBeNull()
        ->and(array_key_exists('github_token', $user->getAttributes()))->toBeFalse()
        ->and(array_key_exists('github_refresh_token', $user->getAttributes()))->toBeFalse();
});

test('an existing GitHub identity signs into the same local account', function () {
    $user = User::factory()->create([
        'github_id' => 'github-456',
        'name' => 'Local Display Name',
        'email' => 'local@example.com',
        'github_username' => 'old-login',
    ]);

    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'github-456',
        'nickname' => 'new-login',
        'name' => 'Remote Display Name',
        'email' => 'changed-on-github@example.com',
        'avatar' => 'https://avatars.githubusercontent.com/u/456',
    ]));

    $this->get(route('auth.github.callback'))
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
    expect($user->refresh())
        ->name->toBe('Local Display Name')
        ->email->toBe('local@example.com')
        ->github_username->toBe('new-login')
        ->avatar_url->toBe('https://avatars.githubusercontent.com/u/456')
        ->and(User::query()->count())->toBe(1);
});

test('a verified GitHub email safely links an existing local account', function () {
    $user = User::factory()->unverified()->create([
        'email' => 'Builder@Example.com',
        'github_id' => null,
        'github_username' => null,
        'avatar_url' => null,
    ]);

    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'github-789',
        'nickname' => 'laravel-builder',
        'email' => 'builder@example.com',
        'avatar' => 'https://avatars.githubusercontent.com/u/789',
    ]));

    $this->get(route('auth.github.callback'))
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
    expect($user->refresh())
        ->github_id->toBe('github-789')
        ->github_username->toBe('laravel-builder')
        ->email_verified_at->not->toBeNull()
        ->and(User::query()->count())->toBe(1);
});

test('an email already linked to another GitHub identity cannot be relinked', function () {
    $user = User::factory()->create([
        'email' => 'linked@example.com',
        'github_id' => 'github-original',
    ]);

    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'github-attacker',
        'nickname' => 'different-account',
        'email' => 'linked@example.com',
    ]));

    $this->get(route('auth.github.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('github');

    $this->assertGuest();
    expect($user->refresh()->github_id)->toBe('github-original')
        ->and(User::query()->count())->toBe(1);
});

test('a GitHub username collision receives a stable unique suffix', function () {
    User::factory()->create(['username' => 'octocat']);

    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'github-collision',
        'nickname' => 'octocat',
        'email' => 'another-octocat@example.com',
    ]));

    $this->get(route('auth.github.callback'))
        ->assertRedirect(route('legal.acceptance.show'));

    $createdUser = User::query()->where('github_id', 'github-collision')->firstOrFail();

    expect($createdUser->username)
        ->not->toBe('octocat')
        ->toMatch('/^octocat-[a-f0-9]{7}$/');
});

test('GitHub authentication requires a verified primary email', function () {
    Socialite::fake('github', SocialiteUser::fake([
        'id' => 'github-no-email',
        'nickname' => 'private-email',
        'email' => null,
    ]));

    $this->get(route('auth.github.callback'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('github');

    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
});

test('a denied GitHub grant returns to login without creating an account', function () {
    $this->get(route('auth.github.callback', ['error' => 'access_denied']))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('github');

    $this->assertGuest();
    expect(User::query()->count())->toBe(0);
});
