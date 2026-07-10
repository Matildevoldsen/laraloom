<?php

namespace App\Ai\Agents;

use App\Ai\Tools\SearchLaravelWeb;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Azure)]
#[MaxSteps(6)]
#[MaxTokens(5000)]
#[Temperature(0.1)]
#[Timeout(120)]
class TodayInLaravelAgent implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are the editor of Today in Laravel, a community-first feed for Laravel developers.

Search only through the provided tool. Include an item only when it is recent, materially about Laravel or its direct ecosystem, useful to working developers, and supported by the returned title, URL, and snippet. Prefer original authors and primary release sources. Reject duplicate announcements, scraped copies, listicles, job ads, generic PHP content, SEO pages, outrage bait, and anything whose central claim cannot be verified from the supplied evidence.

Never reproduce article prose. Write an original factual summary of at most two sentences and an original one-sentence explanation of why the item matters. Never invent an author, release detail, package version, quotation, or capability. Preserve the canonical source URL. Confidence must reflect evidence quality, not writing quality. Use an empty string when an author is unavailable.

Allowed kinds: article, package, project, video, podcast, event, social. Return no more than eight items, ordered by community value rather than engagement.
INSTRUCTIONS;
    }

    /** @return Tool[] */
    public function tools(): iterable
    {
        return [app(SearchLaravelWeb::class)];
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'items' => $schema->array()
                ->items($schema->object(fn (JsonSchema $item): array => [
                    'title' => $item->string()->required(),
                    'url' => $item->string()->required(),
                    'summary' => $item->string()->required(),
                    'why_it_matters' => $item->string()->required(),
                    'kind' => $item->string()->enum(['article', 'package', 'project', 'video', 'podcast', 'event', 'social'])->required(),
                    'source_name' => $item->string()->required(),
                    'source_author' => $item->string()->required(),
                    'tags' => $item->array()->items($item->string())->required(),
                    'confidence' => $item->integer()->min(0)->max(100)->required(),
                    'include' => $item->boolean()->required(),
                ]))
                ->required(),
        ];
    }
}
