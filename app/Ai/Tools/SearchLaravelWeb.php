<?php

namespace App\Ai\Tools;

use App\Models\Source;
use App\Services\TavilyClient;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchLaravelWeb implements Tool
{
    public function __construct(private readonly TavilyClient $tavily) {}

    public function description(): Stringable|string
    {
        return 'Search the last week of approved Laravel ecosystem sources. Returns only titles, URLs, short search snippets, and relevance scores. Use this to discover current, source-backed items.';
    }

    public function handle(Request $request): Stringable|string
    {
        $domains = Source::query()
            ->where('is_active', true)
            ->where('allows_search', true)
            ->pluck('domain')
            ->all();

        $results = $this->tavily->search((string) $request['query'], $domains);

        return json_encode($results, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    /** @return array<string, Type> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('A narrow query for recent Laravel ecosystem news, releases, projects, or learning resources.')
                ->required(),
        ];
    }
}
