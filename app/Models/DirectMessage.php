<?php

namespace App\Models;

use Database\Factories\DirectMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $direct_conversation_id
 * @property int $sender_id
 * @property string $client_id
 * @property string $body
 * @property Carbon|null $created_at
 * @property-read User $sender
 */
#[Fillable(['direct_conversation_id', 'sender_id', 'client_id', 'body'])]
class DirectMessage extends Model
{
    /** @use HasFactory<DirectMessageFactory> */
    use HasFactory;

    /** @return BelongsTo<DirectConversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(DirectConversation::class, 'direct_conversation_id');
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['body' => 'encrypted'];
    }
}
