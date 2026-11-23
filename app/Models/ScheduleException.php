<?php

namespace App\Models;

use App\Casts\DateOnly;
use Database\Factories\ScheduleExceptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A change to one regular airing of a slot: cancelled when `rescheduled_to`
 * is empty, otherwise moved to that moment (same duration and tag).
 */
#[Fillable(['schedule_slot_id', 'date', 'rescheduled_to'])]
class ScheduleException extends Model
{
    /** @use HasFactory<ScheduleExceptionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => DateOnly::class,
            'rescheduled_to' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<ScheduleSlot, $this>
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(ScheduleSlot::class, 'schedule_slot_id');
    }

    public function isCancellation(): bool
    {
        return $this->rescheduled_to === null;
    }
}
