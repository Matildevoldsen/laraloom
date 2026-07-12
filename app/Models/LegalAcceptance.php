<?php

namespace App\Models;

use Database\Factories\LegalAcceptanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $terms_version
 * @property string $privacy_version
 * @property int $minimum_age
 * @property Carbon $accepted_at
 */
#[Fillable(['terms_version', 'privacy_version', 'minimum_age', 'accepted_at'])]
class LegalAcceptance extends Model
{
    /** @use HasFactory<LegalAcceptanceFactory> */
    use HasFactory;

    public $timestamps = false;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'minimum_age' => 'integer',
            'accepted_at' => 'immutable_datetime',
        ];
    }
}
