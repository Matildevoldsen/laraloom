<?php

namespace App\Console\Commands;

use App\Actions\SyncPostReferencesAction;
use App\Models\Mention;
use App\Models\Post;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

#[Signature('posts:sync-references {--chunk=500 : Posts to process per database chunk}')]
#[Description('Synchronize stored hashtags and mentions for existing posts')]
class SyncPostReferences extends Command
{
    public function handle(SyncPostReferencesAction $syncReferences): int
    {
        $chunkSize = (int) $this->option('chunk');

        if ($chunkSize < 1 || $chunkSize > 2000) {
            $this->components->error('The chunk size must be between 1 and 2000.');

            return self::FAILURE;
        }

        $processed = 0;

        Post::query()
            ->select(['id', 'body'])
            ->whereNotNull('body')
            ->chunkById($chunkSize, function (Collection $posts) use ($syncReferences, &$processed): void {
                DB::transaction(function () use ($posts, $syncReferences, &$processed): void {
                    Mention::withoutEvents(function () use ($posts, $syncReferences, &$processed): void {
                        foreach ($posts as $post) {
                            $syncReferences->execute($post);
                            $processed++;
                        }
                    });
                });
            });

        $this->components->info("Synchronized references for {$processed} posts.");

        return self::SUCCESS;
    }
}
