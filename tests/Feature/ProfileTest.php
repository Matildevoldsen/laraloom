<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_displays_community_identity(): void
    {
        $user = User::factory()->create([
            'name' => 'Laravel Maker',
            'username' => 'maker',
            'headline' => 'Shipping useful things.',
            'stack' => ['Laravel', 'Livewire'],
        ]);

        $this->get(route('profiles.show', $user))
            ->assertOk()
            ->assertSee('Laravel Maker')
            ->assertSee('@maker')
            ->assertSee('Livewire');
    }

    public function test_linked_profile_displays_public_github_activity(): void
    {
        Http::fake([
            'api.github.com/users/sourcefolk-maker' => Http::response([
                'public_repos' => 79,
            ]),
            'api.github.com/search/commits*' => Http::response([
                'total_count' => 830,
                'incomplete_results' => false,
                'items' => [[
                    'sha' => 'dfaa4f12067c75cc3fc91805d75d0fffc65f41d1',
                    'html_url' => 'https://github.com/sourcefolk-maker/package/commit/dfaa4f12067c75cc3fc91805d75d0fffc65f41d1',
                    'repository' => ['full_name' => 'sourcefolk-maker/package'],
                    'commit' => [
                        'message' => 'Ship a useful package',
                        'author' => ['date' => '2026-07-12T12:00:00Z'],
                    ],
                ]],
            ]),
        ]);

        $user = User::factory()->create([
            'username' => 'sourcefolk-maker',
            'github_id' => '123456',
            'github_username' => 'sourcefolk-maker',
        ]);

        $this->get(route('profiles.show', [$user, 'tab' => 'github']))
            ->assertOk()
            ->assertSee('79')
            ->assertSee('830')
            ->assertSee('indexed commits in owned repos')
            ->assertSee('Top commits')
            ->assertSee('Ship a useful package')
            ->assertSee('sourcefolk-maker/package')
            ->assertSee('dfaa4f1');
    }

    public function test_standard_profile_tabs_do_not_wait_for_github(): void
    {
        Http::fake();
        $user = User::factory()->create([
            'github_id' => '123456',
            'github_username' => 'sourcefolk-maker',
        ]);

        $this->get(route('profiles.show', $user))->assertOk();

        Http::assertNothingSent();
    }

    public function test_member_can_update_own_profile_but_not_another_profile(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();
        $payload = [
            'name' => 'Updated Maker',
            'username' => 'updated-maker',
            'headline' => 'Laravel builder',
            'stack' => ['Laravel', 'Laravel AI'],
            'is_available_for_work' => true,
        ];

        $this->actingAs($member)
            ->put(route('profiles.update', $member), $payload)
            ->assertRedirect(route('profiles.show', ['user' => 'updated-maker']));

        $this->assertSame('updated-maker', $member->refresh()->username);
        $this->assertTrue($member->is_available_for_work);

        $this->actingAs($member)
            ->put(route('profiles.update', $other), $payload)
            ->assertForbidden();
    }

    public function test_username_change_follows_the_new_canonical_profile_url(): void
    {
        $member = User::factory()->create([
            'username' => 'matilde',
            'username_changed_at' => null,
        ]);
        $stableUpdateUrl = route('profiles.update', $member);

        $response = $this->actingAs($member)->put($stableUpdateUrl, [
            'name' => $member->name,
            'username' => 'tillythecoder',
        ]);

        $response->assertRedirect(route('profiles.show', ['user' => 'tillythecoder']));

        $this->get(route('profiles.show', ['user' => 'tillythecoder']))
            ->assertOk()
            ->assertSee('@tillythecoder');

        $this->actingAs($member)->put($stableUpdateUrl, [
            'name' => 'Updated from a stale tab',
            'username' => 'tillythecoder',
        ])->assertRedirect(route('profiles.show', ['user' => 'tillythecoder']));

        expect($member->refresh()->name)->toBe('Updated from a stale tab');
    }

    public function test_member_can_follow_and_unfollow_another_member(): void
    {
        $member = User::factory()->create();
        $maker = User::factory()->create();

        $this->actingAs($member)->post(route('profiles.follow', $maker))
            ->assertRedirect()
            ->assertSessionHas('status', "You are now following {$maker->name}.");
        $this->assertTrue($member->following()->whereKey($maker->id)->exists());

        $this->actingAs($member)->post(route('profiles.follow', $maker))
            ->assertRedirect()
            ->assertSessionHas('status', "You unfollowed {$maker->name}.");
        $this->assertFalse($member->following()->whereKey($maker->id)->exists());
    }

    public function test_member_can_upload_a_profile_photo_to_private_object_storage(): void
    {
        Storage::fake('r2');
        $member = User::factory()->create([
            'username' => 'photo-maker',
            'avatar_disk' => 'r2',
            'avatar_path' => 'avatars/old.jpg',
        ]);
        Storage::disk('r2')->put('avatars/old.jpg', 'old-avatar');

        $this->actingAs($member)
            ->put(route('profiles.update', $member), [
                'name' => 'Photo Maker',
                'username' => $member->username,
                'headline' => 'Making useful Laravel tools.',
                'bio' => 'A longer profile description.',
                'avatar' => UploadedFile::fake()->image('profile.jpg', 300, 300),
            ])
            ->assertRedirect();

        $member->refresh();

        expect($member->avatar_disk)->toBe('r2')
            ->and($member->avatar_path)->not->toBeNull()
            ->and($member->name)->toBe('Photo Maker')
            ->and($member->bio)->toBe('A longer profile description.');
        Storage::disk('r2')->assertExists($member->avatar_path);
        Storage::disk('r2')->assertMissing('avatars/old.jpg');
    }

    public function test_username_can_only_change_once_per_month(): void
    {
        $member = User::factory()->create([
            'username' => 'first-name',
            'username_changed_at' => null,
        ]);

        $this->actingAs($member)
            ->put(route('profiles.update', $member), [
                'name' => $member->name,
                'username' => 'second-name',
            ])
            ->assertRedirect(route('profiles.show', ['user' => 'second-name']));

        expect($member->refresh()->username_changed_at)->not->toBeNull();

        $this->actingAs($member)
            ->from(route('profiles.edit', $member))
            ->put(route('profiles.update', $member), [
                'name' => $member->name,
                'username' => 'third-name',
            ])
            ->assertRedirect(route('profiles.edit', $member))
            ->assertSessionHasErrors('username');

        expect($member->refresh()->username)->toBe('second-name');
    }

    public function test_profile_fields_remain_editable_during_username_cooldown(): void
    {
        $member = User::factory()->create([
            'username' => 'steady-name',
            'username_changed_at' => now(),
        ]);

        $this->actingAs($member)
            ->put(route('profiles.update', $member), [
                'name' => 'A New Display Name',
                'username' => 'steady-name',
                'headline' => 'A clearer profile description.',
            ])
            ->assertRedirect();

        expect($member->refresh()->name)->toBe('A New Display Name')
            ->and($member->headline)->toBe('A clearer profile description.')
            ->and($member->username)->toBe('steady-name');
    }

    public function test_linked_github_username_cannot_be_changed_from_the_profile_form(): void
    {
        $member = User::factory()->create([
            'github_id' => '123456',
            'github_username' => 'linked-maker',
        ]);

        $this->actingAs($member)
            ->put(route('profiles.update', $member), [
                'name' => $member->name,
                'username' => $member->username,
                'github_username' => 'another-account',
            ])
            ->assertRedirect();

        expect($member->refresh()->github_username)->toBe('linked-maker');
    }

    public function test_username_can_change_after_the_monthly_cooldown(): void
    {
        $member = User::factory()->create([
            'username' => 'old-name',
            'username_changed_at' => now()->subMonth()->subMinute(),
        ]);

        $this->actingAs($member)
            ->put(route('profiles.update', $member), [
                'name' => $member->name,
                'username' => 'available-name',
            ])
            ->assertRedirect(route('profiles.show', ['user' => 'available-name']));

        expect($member->refresh()->username)->toBe('available-name');
    }

    public function test_profile_offers_messages_only_when_the_viewed_member_follows_the_viewer(): void
    {
        $viewer = User::factory()->create();
        $follower = User::factory()->create(['name' => 'Eligible Follower']);
        $other = User::factory()->create(['name' => 'Not A Follower']);
        $follower->following()->attach($viewer);

        $this->actingAs($viewer)
            ->get(route('profiles.show', $follower))
            ->assertOk()
            ->assertSee('Message')
            ->assertSee(route('direct-messages.store', ['recipient' => $follower]), false);

        $this->actingAs($viewer)
            ->get(route('profiles.show', $other))
            ->assertOk()
            ->assertDontSee(route('direct-messages.store', ['recipient' => $other]), false);
    }
}
