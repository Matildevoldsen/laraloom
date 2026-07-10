<?php

namespace App\Models;

use App\SourceMethod;
use Database\Factories\SourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $domain
 * @property SourceMethod $method
 * @property string $homepage_url
 * @property string|null $feed_url
 * @property bool $allows_search
 * @property bool $allows_summary
 * @property bool $is_active
 * @property Carbon|null $permission_checked_at
 * @property Carbon|null $last_discovered_at
 * @property string|null $notes
 */
#[Fillable([
    'name', 'domain', 'method', 'homepage_url', 'feed_url', 'allows_search',
    'allows_summary', 'is_active', 'permission_checked_at',
    'last_discovered_at', 'notes',
])]
class Source extends Model
{
    /** @use HasFactory<SourceFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'method' => SourceMethod::class,
            'allows_search' => 'boolean',
            'allows_summary' => 'boolean',
            'is_active' => 'boolean',
            'permission_checked_at' => 'datetime',
            'last_discovered_at' => 'datetime',
        ];
    }
}
