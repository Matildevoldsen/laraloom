<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\PostKind;
use App\PostStatus;
use App\ProjectKind;
use App\ProjectStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CommunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $editor = User::query()->updateOrCreate(
            ['email' => 'editor@laraloom.local'],
            [
                'name' => 'Laraloom',
                'username' => 'laraloom',
                'password' => Hash::make(Str::password(48)),
                'email_verified_at' => now(),
                'headline' => 'The open home for the Laravel community.',
                'bio' => 'Discover what Laravel developers are reading, building, and shipping.',
                'website_url' => config('app.url'),
                'stack' => ['Laravel', 'Livewire', 'Laravel AI'],
                'is_admin' => true,
            ],
        );

        Project::query()->updateOrCreate(
            ['slug' => 'laraloom'],
            [
                'user_id' => $editor->id,
                'kind' => ProjectKind::Application,
                'status' => ProjectStatus::Published,
                'name' => 'Laraloom',
                'tagline' => 'Everything Laravel, woven together.',
                'description' => 'An open-source community feed, people directory, project launchpad, and carefully sourced daily Laravel briefing.',
                'url' => config('app.url'),
                'tags' => ['Laravel 13', 'Livewire 4', 'Laravel AI', 'Open source'],
                'is_open_source' => true,
                'published_at' => now(),
            ],
        );

        $this->seedWelcomePost($editor);
    }

    private function seedWelcomePost(User $editor): void
    {
        $url = 'https://laravel.com/ai';

        Post::query()->updateOrCreate(
            ['canonical_url_hash' => hash('sha256', $url), 'is_ai_curated' => false],
            [
                'user_id' => $editor->id,
                'kind' => PostKind::Project,
                'status' => PostStatus::Published,
                'title' => 'Welcome to Laraloom',
                'slug' => 'welcome-to-laraloom',
                'body' => 'A community-built home for Laravel people, packages, projects, and the ideas worth carrying forward.',
                'url' => $url,
                'tags' => ['Community', 'Open source', 'Laravel'],
                'published_at' => now(),
            ],
        );
    }
}
