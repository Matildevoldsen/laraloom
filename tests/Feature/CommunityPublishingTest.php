<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\PostKind;
use App\ProjectKind;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_post_composer_only_shows_loading_state_while_submitting(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('x-on:submit="submitting = true"', false)
            ->assertSee('x-bind:disabled="submitting || (! body.trim() && attachments.length === 0)"', false)
            ->assertSee('x-show="! submitting"', false)
            ->assertSee('x-show="submitting"', false)
            ->assertDontSee('data-flux-loading-indicator', false);
    }

    public function test_post_composer_accepts_images_pasted_from_the_clipboard(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('composerForm({', false)
            ->assertSee('x-on:paste="pasteAttachments($event)"', false)
            ->assertSee('x-ref="attachments"', false)
            ->assertSee('x-for="attachment in attachmentItems"', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee('data-flux-error', false);
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

    public function test_member_can_publish_a_post_with_photo_and_video_attachments(): void
    {
        Storage::fake('r2');
        $member = User::factory()->create();

        $this->actingAs($member)->post(route('posts.store'), [
            'kind' => PostKind::Note->value,
            'attachments' => [
                UploadedFile::fake()->image('cloud-launch.jpg', 1200, 800),
                UploadedFile::fake()->create('iphone-photo.heic', 1024, 'image/heic'),
                UploadedFile::fake()->create('demo.mp4', 2048, 'video/mp4'),
            ],
        ])->assertRedirect(route('home'));

        $post = Post::query()->with('attachments')->sole();

        $this->assertCount(3, $post->attachments);
        $this->assertSame(['image', 'image', 'video'], $post->attachments->pluck('media_type')->all());
        foreach ($post->attachments as $attachment) {
            Storage::disk('r2')->assertExists($attachment->path);
        }
    }

    public function test_post_attachment_rejects_unsupported_files(): void
    {
        Storage::fake('r2');
        $member = User::factory()->create();

        $this->actingAs($member)->post(route('posts.store'), [
            'kind' => PostKind::Note->value,
            'attachments' => [UploadedFile::fake()->create('payload.php', 5, 'application/x-php')],
        ])->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseCount('posts', 0);
        Storage::disk('r2')->assertDirectoryEmpty('/');
    }

    public function test_member_can_publish_any_post_type_without_a_title_or_url(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->post(route('posts.store'), [
            'kind' => PostKind::Article->value,
            'body' => 'The body is enough to publish this post.',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('posts', [
            'kind' => PostKind::Article->value,
            'title' => null,
            'url' => null,
        ]);
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
