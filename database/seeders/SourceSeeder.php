<?php

namespace Database\Seeders;

use App\Models\Source;
use App\SourceMethod;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            ['Laravel', 'laravel.com', 'https://laravel.com', SourceMethod::TavilyMetadata],
            ['Laravel News', 'laravel-news.com', 'https://laravel-news.com', SourceMethod::Rss],
            ['Laravel.io', 'laravel.io', 'https://laravel.io', SourceMethod::Rss],
            ['Livewire', 'livewire.laravel.com', 'https://livewire.laravel.com', SourceMethod::TavilyMetadata],
            ['Filament', 'filamentphp.com', 'https://filamentphp.com', SourceMethod::TavilyMetadata],
            ['GitHub', 'github.com', 'https://github.com', SourceMethod::Api],
            ['Packagist', 'packagist.org', 'https://packagist.org', SourceMethod::Api],
        ];

        foreach ($sources as [$name, $domain, $homepage, $method]) {
            Source::query()->updateOrCreate(
                ['domain' => $domain],
                [
                    'name' => $name,
                    'method' => $method,
                    'homepage_url' => $homepage,
                    'allows_search' => true,
                    'allows_summary' => false,
                    'is_active' => true,
                    'permission_checked_at' => now(),
                    'notes' => 'Metadata-only discovery. No raw page content is retained.',
                ],
            );
        }
    }
}
