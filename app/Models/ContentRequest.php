<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type', 'content_url', 'requester_name', 'requester_email',
    'relationship', 'details', 'status', 'resolved_at',
])]
class ContentRequest extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }
}
