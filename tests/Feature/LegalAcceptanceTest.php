<?php

use App\Models\LegalAcceptance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps the legal documents and acceptance form available before acceptance', function (): void {
    $user = User::factory()->withoutLegalAcceptance()->create();

    $this->actingAs($user)
        ->get(route('legal.acceptance.show'))
        ->assertOk()
        ->assertSee('One last step')
        ->assertSee('I confirm that I am at least 18 years old.')
        ->assertSee('The Privacy Policy is a notice, not a request for consent.')
        ->assertSee(route('legal.terms'), escape: false)
        ->assertSee(route('legal.privacy'), escape: false);

    $this->get(route('legal.terms'))->assertOk();
    $this->get(route('legal.privacy'))->assertOk();
});

it('requires separate terms and age confirmations', function (): void {
    $user = User::factory()->withoutLegalAcceptance()->create();

    $this->actingAs($user)
        ->post(route('legal.acceptance.store'), [
            'terms_accepted' => '0',
            'age_confirmed' => '0',
        ])
        ->assertSessionHasErrors(['terms_accepted', 'age_confirmed']);

    expect(LegalAcceptance::query()->whereBelongsTo($user)->exists())->toBeFalse();
});

it('records an immutable versioned acceptance and returns to the intended page', function (): void {
    $user = User::factory()->withoutLegalAcceptance()->create();

    $this->actingAs($user)
        ->get(route('posts.create'))
        ->assertRedirect(route('legal.acceptance.show'));

    $this->post(route('legal.acceptance.store'), [
        'terms_accepted' => '1',
        'age_confirmed' => '1',
    ])->assertRedirect(route('posts.create'));

    $acceptance = LegalAcceptance::query()->whereBelongsTo($user)->sole();

    expect($acceptance)
        ->terms_version->toBe(config('legal.terms_version'))
        ->privacy_version->toBe(config('legal.privacy_version'))
        ->minimum_age->toBe(18)
        ->accepted_at->not->toBeNull();

    $originalAcceptedAt = $acceptance->accepted_at;

    $this->post(route('legal.acceptance.store'), [
        'terms_accepted' => '1',
        'age_confirmed' => '1',
    ])->assertRedirect(route('home'));

    expect(LegalAcceptance::query()->whereBelongsTo($user)->count())->toBe(1)
        ->and($acceptance->refresh()->accepted_at->equalTo($originalAcceptedAt))->toBeTrue();
});

it('requires renewed acceptance when the terms version changes without erasing history', function (): void {
    config()->set('legal.terms_version', '2026-07-12');
    $user = User::factory()->create();

    config()->set('legal.terms_version', '2026-08-01');

    $this->actingAs($user)
        ->get(route('direct-messages.index'))
        ->assertRedirect(route('legal.acceptance.show'));

    $this->post(route('legal.acceptance.store'), [
        'terms_accepted' => '1',
        'age_confirmed' => '1',
    ])->assertRedirect(route('direct-messages.index'));

    expect($user->legalAcceptances()->orderBy('accepted_at')->pluck('terms_version')->all())
        ->toBe(['2026-07-12', '2026-08-01']);
});

it('returns a precondition response for unaccepted json requests', function (): void {
    $user = User::factory()->withoutLegalAcceptance()->create();

    $this->actingAs($user)
        ->getJson(route('direct-messages.index'))
        ->assertStatus(428)
        ->assertJson([
            'message' => 'You must accept the current Terms of Service before using this feature.',
            'acceptance_url' => route('legal.acceptance.show'),
        ]);
});

it('does not expose password registration on the web', function (): void {
    $this->post('/register', [
        'name' => 'Ada Lovelace',
        'username' => 'ada_lovelace',
        'email' => 'ada@example.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertMethodNotAllowed();

    $this->assertGuest();
    expect(LegalAcceptance::query()->count())->toBe(0);
});
