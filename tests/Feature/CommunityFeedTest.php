<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\PostStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_feed_shows_published_content_and_hides_drafts(): void
    {
        $published = Post::factory()->create(['title' => 'Laravel releases something excellent']);
        $draft = Post::factory()->create(['title' => 'Private draft', 'status' => PostStatus::Draft]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title)
            ->assertSee('Everything happening in Laravel');
    }

    public function test_following_feed_only_shows_people_the_member_follows(): void
    {
        $member = User::factory()->create();
        $followed = User::factory()->create();
        $other = User::factory()->create();
        $member->following()->attach($followed);

        $wanted = Post::factory()->for($followed)->create(['title' => 'From a followed maker']);
        $unwanted = Post::factory()->for($other)->create(['title' => 'From someone else']);

        $this->actingAs($member)
            ->get(route('home', ['feed' => 'following']))
            ->assertOk()
            ->assertSee($wanted->title)
            ->assertDontSee($unwanted->title);
    }

    public function test_feed_searches_posts(): void
    {
        Post::factory()->create(['title' => 'Precognition patterns']);
        Post::factory()->create(['title' => 'Unrelated release']);

        $this->get(route('home', ['q' => 'Precognition']))
            ->assertOk()
            ->assertSee('Precognition patterns')
            ->assertDontSee('Unrelated release');
    }

    public function test_feed_includes_projects_and_people_rail_data(): void
    {
        $user = User::factory()->create(['name' => 'Taylor Community']);
        $project = Project::factory()->for($user)->create(['name' => 'Cloud Loom']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($project->name)
            ->assertSee($user->name);
    }

    public function test_people_to_know_excludes_self_and_existing_follows_and_offers_follow_actions(): void
    {
        $member = User::factory()->create(['name' => 'Signed In Member']);
        $alreadyFollowed = User::factory()->create(['name' => 'Already Followed']);
        $suggested = User::factory()->create(['name' => 'Suggested Maker']);
        $member->following()->attach($alreadyFollowed);

        $this->actingAs($member)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('Signed In Member')
            ->assertDontSee('Already Followed')
            ->assertSee('Suggested Maker')
            ->assertSee(route('profiles.follow', $suggested), false)
            ->assertSee('Follow');
    }
}
