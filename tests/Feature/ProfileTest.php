<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_member_can_follow_and_unfollow_another_member(): void
    {
        $member = User::factory()->create();
        $maker = User::factory()->create();

        $this->actingAs($member)->post(route('profiles.follow', $maker))->assertRedirect();
        $this->assertTrue($member->following()->whereKey($maker->id)->exists());

        $this->actingAs($member)->post(route('profiles.follow', $maker))->assertRedirect();
        $this->assertFalse($member->following()->whereKey($maker->id)->exists());
    }
}
