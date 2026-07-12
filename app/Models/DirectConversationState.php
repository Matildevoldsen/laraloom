<?php

namespace App\Models;

use Database\Factories\DirectConversationStateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $direct_conversation_id
 * @property int $user_id
 * @property int|null $last_read_message_id
 * @property Carbon|null $last_read_at
 * @property Carbon|null $archived_at
 */
#[Fillable([
    'direct_conversation_id',
    'user_id',
    'last_read_message_id',
    'last_read_at',
    'archived_at',
])]
class DirectConversationState extends Model
{
    /** @use HasFactory<DirectConversationStateFactory> */
    use HasFactory;

    /** @return BelongsTo<DirectConversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(DirectConversation::class, 'direct_conversation_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<DirectMessage, $this> */
    public function lastReadMessage(): BelongsTo
    {
        return $this->belongsTo(DirectMessage::class, 'last_read_message_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }
}
