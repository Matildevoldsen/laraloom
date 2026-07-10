<?php

namespace App\Jobs;

use App\Actions\PublishCuratedPostAction;
use App\Ai\Agents\TodayInLaravelAgent;
use App\Data\CuratedItem;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Laravel\Ai\Responses\StructuredAgentResponse;
use RuntimeException;

class DiscoverLaravelContent implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 180;

    public int $uniqueFor = 1800;

    public function handle(PublishCuratedPostAction $publish): void
    {
        $queries = config('curation.queries', []);

        if (! is_array($queries) || $queries === []) {
            throw new RuntimeException('No curation queries are configured.');
        }

        $response = TodayInLaravelAgent::make()->prompt(
            'Today is '.now()->toDateString().". Search for the strongest recent Laravel ecosystem items using these editorial beats:\n- ".implode("\n- ", $queries)
        );
        if (! $response instanceof StructuredAgentResponse) {
            throw new RuntimeException('The curation agent did not return structured output.');
        }

        $items = $response->toArray()['items'] ?? null;

        if (! is_array($items)) {
            throw new RuntimeException('The curation agent returned an invalid item list.');
        }

        foreach ($items as $item) {
            if (is_array($item)) {
                $curatedItem = CuratedItem::fromArray($item);

                if ($curatedItem instanceof CuratedItem) {
                    $publish->execute($curatedItem);
                }
            }
        }
    }
}
