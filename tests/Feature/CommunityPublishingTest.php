<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\PostKind;
use App\ProjectKind;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_publish_a_post_with_normalized_attribution_link(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->post(route('posts.store'), [
            'kind' => PostKind::Article->value,
            'title' => 'A useful Laravel article',
            'body' => 'My original commentary on the article.',
            'url' => 'https://example.com/article?utm_source=noise&ref=community',
            'tags' => 'Laravel, Community',
        ])->assertRedirect(route('home'));

        $post = Post::query()->sole();
        $this->assertSame('https://example.com/article?ref=community', $post->url);
        $this->assertSame(['Laravel', 'Community'], $post->tags);
        $this->assertSame($member->id, $post->user_id);
    }

    public function test_member_can_publish_a_laravel_cloud_project(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->post(route('projects.store'), [
            'kind' => ProjectKind::Application->value,
            'name' => 'Loom Cloud',
            'tagline' => 'A community project on Laravel Cloud.',
            'description' => 'A proper description of the project and what it does.',
            'url' => 'https://loom.example.com',
            'laravel_cloud_url' => 'https://loom.laravel.cloud',
            'is_open_source' => true,
            'tags' => 'Cloud, Open source',
        ]);

        $project = Project::query()->sole();
        $response->assertRedirect(route('projects.show', $project));
        $this->assertTrue($project->is_open_source);
    }

    public function test_project_rejects_a_fake_laravel_cloud_hostname(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->post(route('projects.store'), [
            'kind' => ProjectKind::Application->value,
            'name' => 'Not Cloud',
            'tagline' => 'This URL is trying to look official.',
            'description' => 'A long enough project description for validation.',
            'url' => 'https://example.com',
            'laravel_cloud_url' => 'https://laravel.cloud.example.com',
        ])->assertSessionHasErrors('laravel_cloud_url');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_guests_cannot_publish(): void
    {
        $this->post(route('posts.store'), [])->assertRedirect(route('login'));
        $this->post(route('projects.store'), [])->assertRedirect(route('login'));
    }
}
