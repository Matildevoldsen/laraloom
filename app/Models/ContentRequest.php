<?php

namespace App\Models;

use App\ContentRequestStatus;
use App\ContentRequestType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'type', 'content_url', 'requester_name', 'requester_email',
    'relationship', 'details', 'status', 'resolution_notes',
    'status_updated_by', 'resolved_at',
])]
class ContentRequest extends Model
{
    public function reference(): string
    {
        return 'LR-'.Str::padLeft((string) $this->getKey(), 6, '0');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => ContentRequestType::class,
            'status' => ContentRequestStatus::class,
            'resolved_at' => 'datetime',
        ];
    }
}
