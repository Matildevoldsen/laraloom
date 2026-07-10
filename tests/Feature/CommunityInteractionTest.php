<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_react_and_bookmark_without_duplicate_records(): void
    {
        $member = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($member)->post(route('posts.reaction', $post))->assertRedirect();
        $this->actingAs($member)->post(route('posts.bookmark', $post))->assertRedirect();

        $this->assertDatabaseHas('reactions', ['user_id' => $member->id, 'post_id' => $post->id]);
        $this->assertDatabaseHas('bookmarks', ['user_id' => $member->id, 'post_id' => $post->id]);

        $this->actingAs($member)->post(route('posts.reaction', $post))->assertRedirect();
        $this->actingAs($member)->post(route('posts.bookmark', $post))->assertRedirect();

        $this->assertDatabaseCount('reactions', 0);
        $this->assertDatabaseCount('bookmarks', 0);
    }
}
