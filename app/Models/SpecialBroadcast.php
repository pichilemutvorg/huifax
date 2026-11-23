<?php

namespace App\Models;

use App\Enums\BroadcastTag;
use Database\Factories\SpecialBroadcastFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A one-off airing outside the weekly grid (a live event, a special).
 */
#[Fillable(['program_id', 'starts_at', 'duration_minutes', 'tag'])]
class SpecialBroadcast extends Model
{
    /** @use HasFactory<SpecialBroadcastFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'duration_minutes' => 'integer',
            'tag' => BroadcastTag::class,
        ];
    }

    /**
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
