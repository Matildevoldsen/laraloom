<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_guests_to_the_public_community_feed(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('home'));
    }

    public function test_dashboard_redirects_members_to_the_public_community_feed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('home'));
    }
}
