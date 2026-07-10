<?php

namespace App\Console\Commands;

use App\Jobs\DiscoverLaravelContent;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('curation:discover {--sync : Run immediately instead of dispatching to the queue}')]
#[Description('Discover and curate recent Laravel ecosystem content.')]
class DiscoverLaravelContentCommand extends Command
{
    public function handle(): int
    {
        if ((bool) $this->option('sync')) {
            DiscoverLaravelContent::dispatchSync();
            $this->components->info('Laravel ecosystem discovery completed.');

            return self::SUCCESS;
        }

        DiscoverLaravelContent::dispatch();
        $this->components->info('Laravel ecosystem discovery queued.');

        return self::SUCCESS;
    }
}
