<?php

namespace App\Models;

use App\Casts\DateOnly;
use App\Enums\BroadcastTag;
use App\Schedule\Weekdays;
use Carbon\CarbonImmutable;
use Database\Factories\ScheduleSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A weekly recurring airing: a program on some weekdays at a fixed local
 * time, optionally limited to a season (valid_from / valid_until).
 */
#[Fillable(['program_id', 'weekdays', 'start_time', 'duration_minutes', 'tag', 'valid_from', 'valid_until', 'is_enabled'])]
class ScheduleSlot extends Model
{
    /** @use HasFactory<ScheduleSlotFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weekdays' => 'array',
            'duration_minutes' => 'integer',
            'tag' => BroadcastTag::class,
            'valid_from' => DateOnly::class,
            'valid_until' => DateOnly::class,
            'is_enabled' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * @return HasMany<ScheduleException, $this>
     */
    public function exceptions(): HasMany
    {
        return $this->hasMany(ScheduleException::class);
    }

    /**
     * Whether the slot has a regular airing on the given date (ignoring exceptions).
     */
    public function airsOn(CarbonImmutable $date): bool
    {
        return in_array($date->isoWeekday(), array_map('intval', $this->weekdays ?? []), true)
            && ($this->valid_from === null || $date->toDateString() >= $this->valid_from->toDateString())
            && ($this->valid_until === null || $date->toDateString() <= $this->valid_until->toDateString());
    }

    /**
     * Start of the regular airing on the given date, in the app timezone.
     */
    public function startOn(CarbonImmutable $date): CarbonImmutable
    {
        return CarbonImmutable::parse("{$date->toDateString()} {$this->start_time}", config('app.timezone'));
    }

    /**
     * Short description for the admin panel, e.g. "News · lun., mié. · 21:00".
     */
    public function label(): string
    {
        return implode(' · ', [
            $this->program?->name,
            Weekdays::summary($this->weekdays ?? []),
            substr((string) $this->start_time, 0, 5),
        ]);
    }

    /**
     * Only slots enabled in the admin panel.
     */
    #[Scope]
    protected function enabled(Builder $query): void
    {
        $query->where('is_enabled', true);
    }
}
