<?php

namespace App\Models;

use Database\Factories\DirectConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * @property int $id
 * @property int $participant_one_id
 * @property int $participant_two_id
 * @property int $initiated_by_id
 * @property Carbon|null $last_message_at
 * @property-read User $participantOne
 * @property-read User $participantTwo
 * @property-read DirectMessage|null $latestMessage
 */
#[Fillable([
    'participant_one_id',
    'participant_two_id',
    'initiated_by_id',
    'last_message_at',
])]
class DirectConversation extends Model
{
    /** @use HasFactory<DirectConversationFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function participantOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_one_id');
    }

    /** @return BelongsTo<User, $this> */
    public function participantTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_two_id');
    }

    /** @return BelongsTo<User, $this> */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_id');
    }

    /** @return HasMany<DirectMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(DirectMessage::class);
    }

    /** @return HasOne<DirectMessage, $this> */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(DirectMessage::class)->latestOfMany();
    }

    /** @return HasMany<DirectConversationState, $this> */
    public function states(): HasMany
    {
        return $this->hasMany(DirectConversationState::class);
    }

    /** @param Builder<DirectConversation> $query */
    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where(function (Builder $query) use ($user): void {
            $query->where('participant_one_id', $user->id)
                ->orWhere('participant_two_id', $user->id);
        });
    }

    public function hasParticipant(User $user): bool
    {
        return in_array($user->id, $this->participantIds(), true);
    }

    public function hasActiveFollow(): bool
    {
        return Follow::query()
            ->where(function (Builder $query): void {
                $query->where('follower_id', $this->participant_one_id)
                    ->where('following_id', $this->participant_two_id);
            })
            ->orWhere(function (Builder $query): void {
                $query->where('follower_id', $this->participant_two_id)
                    ->where('following_id', $this->participant_one_id);
            })
            ->exists();
    }

    public function otherParticipant(User $user): User
    {
        if ($user->id === $this->participant_one_id) {
            return $this->participantTwo;
        }

        if ($user->id === $this->participant_two_id) {
            return $this->participantOne;
        }

        throw new LogicException('The user is not a participant in this direct conversation.');
    }

    public function isUnreadFor(User $user): bool
    {
        $latestMessage = $this->latestMessage;
        if ($latestMessage === null || $latestMessage->sender_id === $user->id) {
            return false;
        }

        $state = $this->states->firstWhere('user_id', $user->id);
        $lastReadMessageId = $state instanceof DirectConversationState
            ? $state->last_read_message_id
            : null;

        return (int) ($lastReadMessageId ?? 0) < $latestMessage->id;
    }

    /** @return array{0: int, 1: int} */
    public function participantIds(): array
    {
        return [$this->participant_one_id, $this->participant_two_id];
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }
}
