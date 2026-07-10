# Laraloom

Everything Laravel, woven together.

Laraloom is an open-source community front page for the people, packages, projects, and ideas moving Laravel forward. It combines member publishing and profiles with a carefully bounded “Today in Laravel” discovery agent.

## What makes it different

- **Today in Laravel** — an Azure OpenAI agent finds recent ecosystem signals through Tavily, ranks them for working developers, and explains why each matters.
- **Creator-first discovery** — Laraloom retains only source metadata and short original summaries. It never stores raw article bodies, and every item sends readers to the original publisher.
- **Community graph** — profiles, follows, reactions, bookmarks, original posts, and a directory of Laravel applications, packages, tools, and content.
- **Made with Laravel** — projects can link their repository and verified `*.laravel.cloud` deployment.
- **Visible provenance** — AI-curated items are labelled, attributed, confidence-gated, and deduplicated by canonical URL.

## Trust boundary

Automated discovery is deliberately narrow:

1. Only active domains in the `sources` table may be searched.
2. Tavily receives that allow-list and is called with `include_raw_content=false` and `include_answer=false`.
3. Returned URLs are checked against the allow-list again before the agent sees them.
4. Azure OpenAI receives titles, URLs, and short search snippets—not full pages.
5. Invalid, unattributed, excluded, low-confidence, and duplicate results are rejected before publication.
6. X is not scraped. A future X source must use the official API or oEmbed.

See the public [content principles](resources/views/legal/content-policy.blade.php) for publisher opt-out and takedown rules.

## Stack

- Laravel 13 and PHP 8.3+
- Livewire 4, Flux, Tailwind CSS 4
- Laravel AI SDK with native Azure provider
- Tavily Search API
- PostgreSQL in production, SQLite for local development and tests
- Pest 4, Larastan level 7, Laravel Pint

## Local setup

```bash
composer run setup
php artisan db:seed
composer run dev
```

The seeded editor account uses an intentionally random password and is not a login fixture. Register through the application to test member flows.

To enable the discovery agent, configure:

```dotenv
TAVILY_API_KEY=
AZURE_OPENAI_API_KEY=
AZURE_OPENAI_URL=https://your-resource.openai.azure.com
AZURE_OPENAI_DEPLOYMENT=your-deployment-name
AZURE_OPENAI_API_VERSION=2025-04-01-preview
AZURE_OPENAI_STORE=false
```

Then run one discovery cycle:

```bash
php artisan curation:discover --sync
```

The queued job is scheduled hourly and uses a uniqueness lock to prevent overlapping discovery runs.

## Quality checks

```bash
composer test
npm run build
```

The suite covers community permissions and interactions, profiles, publishing, URL normalization, Laravel Cloud URL validation, content-policy disclosure, source allow-list enforcement, Tavily request privacy, curation thresholds, and deduplication.

## Deployment

Laraloom is designed for Laravel Cloud with a PostgreSQL database and queue worker. Add the Azure and Tavily secrets as encrypted environment variables, run migrations and seed the approved source registry, then keep the scheduler and queue worker active.

## Contributing

Issues and focused pull requests are welcome. New automated sources must document their permitted access method, attribution requirements, and opt-out path. Do not add a scraper for a site that offers an official feed, API, or embed route.

Laraloom is released under the [MIT License](LICENSE.md).
